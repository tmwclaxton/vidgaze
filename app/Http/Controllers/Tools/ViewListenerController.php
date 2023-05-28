<?php

namespace App\Http\Controllers\Tools;
use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Models\LiveClient;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use App\Models\StreamModels\Stream;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoInteraction;
use App\Models\VideoModels\VideoView;
use Illuminate\Http\Request;

class ViewListenerController extends Controller
{
    const LOGGED_OUT_VIEWER_ID = 'empty';
    private Video $video;
    private Stream $stream;
    private PodcastEpisode $podcast;


    public function message(Request $request)
    {

        $data = $request->all();
        $item_id = $data['item_id'] ?? null;
        $type = $data['type'] ?? null;
        $watch_duration = $data['watch_duration'] ?? null; //how long watch
        $token = $data['token'] ?? null;
        $view_point = $data['view_point'] ?? null; //where they watched up to

        // if logged in
        if (!auth()->check()) {
            $viewer_id = self::LOGGED_OUT_VIEWER_ID;
        } else {
            $viewer_id = auth()->user()->id;
        }

        // get session id
        $session_id = $request->session()->getId();

        // Check if a client with the same data exists
        $liveClient = LiveClient::where(['item_id' => $item_id,'type' => $type,'viewer_id' => $viewer_id, 'session_id'])->first();

        // If no client with the same token exists, create a new client
        if (!$liveClient) {
            $liveClient = new LiveClient();
            $liveClient->token = $token;
            $liveClient->item_id = $item_id;
            $liveClient->type = $type;
            $liveClient->save();
            return response()->json(['success' => 'New client created'], 200);
        }

        $liveClient->touch();

        if($liveClient->type === "video") {
            // we only need to find the video model if we haven't already counted the view or live viewer count
            if ($liveClient->live_viewer_counted === false || $liveClient->view_counted === false) {
                $this->video = Video::find($liveClient->item_id);
                // Check if the video exists
                if (!$this->video) {
                    return response()->json(['error' => 'Video not found'], 404);
                }
            }

            // Record the view model
            $this->recordVideoView($viewer_id, $item_id, $session_id, $watch_duration);

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

            //increment view count on video model
            if ( $liveClient->view_counted === false ) {
                if ($viewer_id !== self::LOGGED_OUT_VIEWER_ID ) {
                    // if logged in increment view count threshold is lower

                    // Check if the video has been watched for long enough to count as a view
                    if ($watch_duration >= 5 || $watch_duration >= 0.1 * $this->video->duration) {
                        // Attempt to increment the view count
                        $this->incrementViewCount($liveClient);
                    }

                    // Record the view point only if logged in
                    $this->recordVideoViewPoint($viewer_id, $item_id, $view_point);

                } else {
                    // if not logged in increment view count threshold is higher

                    // Check if the video has been watched for long enough to count as a view
                    if ($watch_duration >= 15 || $watch_duration >= 0.3 * $this->video->duration) {
                        // Attempt to increment the view count

                        $this->incrementViewCount($liveClient);
                    }
                }
            }

            return response()->json(['success' => 'View recorded'], 200);

        }

        if ($liveClient->type === "stream") {
            //define stream //if you don't define it every time the shorts web socket doesn't work
            if ($liveClient->live_viewer_counted === false ) {
                $liveClient->live_viewer_counted = true;
                $liveClient->save();

                $stream = Stream::find($item_id);

                // Increment the live viewer count
                $stream->increment('live_viewer_count', 1);
                $stream->save();

                return response()->json(['success' => 'Stream live viewer count incremented'], 200);


            }
        }

        if ($liveClient->type === "podcast") {
            //define stream //if you don't define it every time the shorts web socket doesn't work
            if ($liveClient->live_viewer_counted === false ) {
                $liveClient->live_viewer_counted = true;
                $liveClient->save();

                $podcast_episode = PodcastEpisode::find($item_id);

                // Increment the live viewer count
                $podcast_episode->increment('live_viewer_count', 1);
                $podcast_episode->save();

                return response()->json(['success' => 'Podcast live viewer count incremented'], 200);
        }
    }

        return response()->json(['error' => 'Invalid type'], 400);
    }

    private function recordVideoViewPoint(string $viewer_id, string $item_id, int $viewPoint): void
    {
        //this records where in the video was watched to
        VideoInteraction::updateOrCreate(
            ['video_id' => $item_id],
            ['viewer_id' => $viewer_id]
        )->update(['view_point' => $viewPoint]);
    }

    private function recordVideoView(string $viewer_id, string $item_id, string $session_id, int $watch_duration): void
    {
        // Retrieve the view for the given viewer, video, and session
        $view = VideoView::where([
            'session_id' => $session_id,
            'video_id' => $item_id,
        ])->orWhere([
            'viewer_id' => $viewer_id,
            'video_id' => $item_id,
        ])->first();
        // If the view does not exist, create a new view if the duration is under 2 minutes
        if ($view === null) {

            if ($watch_duration <= 120) {
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
            }
        } else {
            // If the view exists, update the duration if the difference between the new and old duration is under 20 seconds
            // and the total duration is under 2 minutes since the last update
            $lastUpdated = $view->updated_at;
            $durationDifference = $watch_duration - $view->duration;
            $currentTime = now();
            if ($durationDifference > 0 && $durationDifference <= 20 && $durationDifference <= $lastUpdated->addSeconds(120)->diffInSeconds($currentTime)) {
                $view->update(['duration' => $watch_duration]);
            }
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
