<?php

namespace App\Http\Controllers\ApiControllers;
use App\Enums\Kind;
use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Models\LiveClient;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use App\Models\StreamModels\Stream;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoInteraction;
use App\Models\VideoModels\VideoView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ViewListenerController extends Controller
{
    const LOGGED_OUT_VIEWER_ID = null;
    private Video $video;
    private Stream $stream;
    private PodcastEpisode $podcast;

    public array $allowedKinds = [
        Kind::Video->value,
        Kind::PodcastEpisode->value,
        Kind::Stream->value,
    ];



    /**
     * @throws \Exception
     */
    public function message(Request $request)
    {
        $request->validate([
            'watch_duration' => 'required|int',
            'view_point' => 'required|int',
            'item_id' => 'required|int',
            'type' => 'in:' . implode(',', $this->allowedKinds),
            'client_identifier' => 'required|string',
        ]);


        $data = $request->all();
        $item_id = $data['item_id'] ?? null;
        $type = $data['type'] ?? null;
        $watch_duration = $data['watch_duration'] ?? null; //how long watch
        $view_point = $data['view_point'] ?? null; //where they watched up to

        // if logged in
        if (!auth()->check()) {
            $viewer_id = self::LOGGED_OUT_VIEWER_ID;
        } else {
            $viewer_id = auth()->user()->creator->id;
        }

        // because we are using token based authentication which is stateless this is an alternative to a session id, client comes up with a random string and sends it to the server
        $session_id = $data['client_identifier'] ?? null; // guest id

        // Check if a record with the same data exists
        $liveClient = LiveClient::where(
            [
                'viewer_id' => $viewer_id,
                'session_id' => $session_id,
                'item_id' => $item_id,
                'type' => $type,
            ])->first();

        // If no record with the same token exists, create a new record
        if (!$liveClient) {

            $liveClient = new LiveClient();
            $liveClient->viewer_id = $viewer_id;
            $liveClient->session_id = $session_id;
            $liveClient->item_id = $item_id;
            $liveClient->type = $type;
            $liveClient->save();

            return response()->json(['message' => 'New live client created'], 200);
        }

        $liveClient->touch();

        if ($liveClient->type === "video") {
            return $this->videoLiveClient($liveClient, $item_id, $watch_duration, $view_point);
        }

        if ($liveClient->type === "stream") {
            return $this->streamLiveClient($liveClient, $item_id, $watch_duration, $view_point);
        }

        if ($liveClient->type === "podcast") {
            return $this->podcastLiveClient($liveClient, $item_id, $watch_duration, $view_point);
        }

        return response()->json(['error' => 'Invalid type'], 500);
    }

    private function videoLiveClient(LiveClient $liveClient, string $item_id, int $watch_duration, int $view_point): JsonResponse
    {

        $video = Video::find($liveClient->item_id);
        // Check if the video exists
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        } else {
            $this->video = $video;
        }

        // Record the view model
        [$durationUpdated, $recordVideoViewResponse] = $this->recordVideoView($liveClient->viewer_id, $item_id, $liveClient->session_id, $watch_duration);

        //check if live viewer count has been updated
        if (($liveClient->live_viewer_counted === false)) {
            $liveClient->live_viewer_counted = true;
            $liveClient->save();
            //shorts has a bug so for now only if the video is longer than 60 seconds
            if ($this->video->duration > 60) {
                // Increment the live viewer count
                $this->video->increment('live_viewer_count', 1);
                $this->video->save();
            }
        }

        if (Auth()->check()) {
            // check if view point is less than 1 minute from the end of the video
            if ($view_point > $this->video->duration - 60) {
                // if so record the view point as the start of the video
                $view_point = 0;
            }
            // Record the view point only if logged in
            $view_point_recorded = $this->recordVideoViewPoint($liveClient->viewer_id, $item_id, $view_point);
        } else {
            $view_point_recorded = false;
        }

        //increment view count on video model
        if ($liveClient->view_counted === false) {
            if ($liveClient->viewer_id !== self::LOGGED_OUT_VIEWER_ID) {
                // if logged in increment view count threshold is lower

                // Check if the video has been watched for long enough to count as a view
                if ($watch_duration >= 5 || $watch_duration >= 0.1 * $this->video->duration) {
                    // Attempt to increment the view count
                    $this->incrementViewCount($liveClient);
                }

            } else {
                // if not logged in increment view count threshold is higher

                // Check if the video has been watched for long enough to count as a view
                if ($watch_duration >= 15 || $watch_duration >= 0.3 * $this->video->duration) {
                    // Attempt to increment the view count

                    $this->incrementViewCount($liveClient);
                }
            }
        }

        return response()->json([
            'message' => $recordVideoViewResponse,
            'duration_updated' => $durationUpdated ?? false,
            'view_recorded' => $liveClient->view_counted,
            'view_point_recorded' => $view_point_recorded ?? false,
        ], 200);
    }

    private function streamLiveClient(LiveClient $liveClient, string $item_id, int $watch_duration, int $view_point): JsonResponse
    {
        //define stream //if you don't define it every time the shorts web socket doesn't work
        if ($liveClient->live_viewer_counted === false) {
            $liveClient->live_viewer_counted = true;
            $liveClient->save();

            $stream = Stream::find($item_id);

            // Increment the live viewer count
            $stream->increment('live_viewer_count', 1);
            $stream->save();

            return response()->json([
                'success' => 'Stream live viewer count incremented'
            ], 200);


        }
    }

    private function podcastLiveClient(LiveClient $liveClient, string $item_id, int $watch_duration, int $view_point): JsonResponse
    {
        if ($liveClient->live_viewer_counted === false) {
            $liveClient->live_viewer_counted = true;
            $liveClient->save();

            $podcast_episode = PodcastEpisode::find($item_id);

            // Increment the live viewer count
            $podcast_episode->increment('live_viewer_count', 1);
            $podcast_episode->save();

            return response()->json([
                'success' => 'Podcast live viewer count incremented'
            ], 200);
        }
    }


    private function recordVideoViewPoint(mixed $viewer_id, string $item_id, int $viewPoint): bool
    {
        //this records where in the video was watched to
        if (VideoInteraction::updateOrCreate(
            ['video_id' => $item_id],
            ['viewer_id' => $viewer_id]
        )->update(['view_point' => $viewPoint])) {
            return true;
        }

        return false;
    }

    private function recordVideoView(mixed $viewer_id, string $item_id, string $session_id, int $watch_duration): array
    {
        // Retrieve the view for the given viewer, video, and session within the last 5 minutes otherwise create a new view
        $view = VideoView::where([
            'session_id' => $session_id,
            'video_id' => $item_id,
        ])->where([
            'viewer_id' => $viewer_id,
            'video_id' => $item_id,
        ])->where('created_at', '>=', Carbon::now()->subMinutes(5))->get()->first();


        // If the view does not exist, create a new view if the duration is under 2 minutes
        if ($view === null) {
            if ($watch_duration >= 20) { // check that user isn't trying to cheat the system
                return [false, "Invalid start watch duration above 20 seconds"];
            }
            if ($viewer_id !== self::LOGGED_OUT_VIEWER_ID) {
                //create video view
                $videoView = VideoView::create([
                    'viewer_id' => $viewer_id,
                    'video_id' => $item_id,
                    'session_id' => $session_id,
                    'duration' => $watch_duration
                ]);
            } else {
                $videoView = VideoView::create([
                    'video_id' => $item_id,
                    'session_id' => $session_id,
                    'duration' => $watch_duration
                ]);
            }
            $videoView->save();
            return [true, "Video view created"];

        } else {
            // If the view exists, update the duration if the difference between the new and old duration is under 20 seconds
            // and the total duration is under 2 minutes since the last update
            $lastUpdated = $view->updated_at;
            $durationDifference = $watch_duration - $view->duration;
            $currentTime = now();

            if (!($durationDifference > 0 && $durationDifference <= 20)) {
                return [false, "Video view duration not updated - invalid duration difference of $durationDifference seconds.  Has to be between 0 and 20 seconds"];
            }

            $leeWay = 4; //seconds
            if (!($durationDifference <= $currentTime->diffInSeconds($lastUpdated) + $leeWay)) {
                return [false, "Video view duration not updated - it isn't possible to have watched $durationDifference seconds in the last " . $currentTime->diffInSeconds($lastUpdated) . " seconds"];
            }

            $view->update(['duration' => $watch_duration]);
            return [true, "Video view duration updated"];

        }
    }

    private function incrementViewCount(LiveClient $liveClient): void
    {
        // Check if the view has already been counted for this connection
        if ($liveClient->view_counted === false) {
            $liveClient->view_counted = true;

            $this->video->increment('view_count', 1);
            $this->video->save();
            //this stops a view being count everytime a duration update is sent
            $liveClient->save();

        }
    }
}
