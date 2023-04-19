<?php

namespace App\Http\Controllers\Tools;
use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Models\LiveClient;
use App\Models\Stream;
use App\Models\Video;
use App\Models\VideoViewInfos;
use App\Models\VideoViews;
use App\Services\MixPanelTrackingService;
use Illuminate\Http\Request;

class ViewListenerController extends Controller
{
    const LOGGED_OUT_VIEWER_ID = 'empty';
    private Video $video;

    public function message(Request $request)
    {

        $data = $request->all();
        $token = $data['token'];
        $videoId = $data['video_id'];
        $viewerId = $data['viewerId'];
        $sessionId = $data['sessionId'];
        $viewDuration = $data['viewDuration']; //how long watch

        // Generate a token for the viewer and video using the server-side function
        // this means they can't fake their sessionId or viewerid or video id
        $expectedToken = TokenHelper::generateToken($sessionId, $viewerId, $videoId);
        // Check if the token is valid
        if ($token === $expectedToken) {
            // error_log('passed token check');

            // Check if a client with the same token exists
            $liveClient = LiveClient::where('token', $token)->first();

            // If no client with the same token exists, create a new client
            if (!$liveClient) {
                $liveClient = new LiveClient();
                $liveClient->token = $token;
                $liveClient->item_id = $data['video_id'];
                $liveClient->type = $data['type'];
                $liveClient->save();

                // error_log('new client with ' . $token);
            } else {
                $liveClient->touch();

                if($liveClient->type === "video") {
                    // error_log('video');

                    $viewPoint = $data['viewPoint']; //where they watched up to

                    if ($liveClient->live_viewer_counted === false || $liveClient->view_counted === false) {
                        // error_log('find video model');

                        $this->video = Video::find($liveClient->item_id);
                    }

                    // Record the view model
                    $this->recordView($viewerId, $videoId, $sessionId, $viewDuration);

                    // error_log($liveClient->live_viewer_counted);
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
                        if ($viewerId !== self::LOGGED_OUT_VIEWER_ID ) {
                            // error_log('break 2');

                            // Check if the video has been watched for long enough to count as a view
                            if ($viewDuration >= 5 || $viewDuration >= 0.1 * $this->video->duration) {
                                // Attempt to increment the view count
                                $this->incrementViewCount($viewerId, $liveClient);
                            }

                            // Record the view point only if logged in
                            $this->recordViewPoint($viewerId, $videoId, $viewPoint);

                        } else {
                            // error_log('break 3');

                            // Check if the video has been watched for long enough to count as a view
                            if ($viewDuration >= 15 || $viewDuration >= 0.3 * $this->video->duration) {
                                // Attempt to increment the view count

                                $this->incrementViewCount($viewerId, $liveClient);
                            }
                                // error_log('break 3.1');
                        }
                        // error_log('break 4');
                    }

                } elseif ($liveClient->type === "stream") {
                    //define stream //if you don't define it every time the shorts web socket doesn't work
                    if ($liveClient->live_viewer_counted === false ) {
                        $liveClient->live_viewer_counted = true;
                        $liveClient->save();

                        $stream = Stream::find($videoId);

                        // Increment the live viewer count
                        $stream->increment('live_viewer_count', 1);
                        $stream->save();

                        //get Mixpanel Instance
                        $mp = app(MixPanelTrackingService::class)->mixPanel;
                        // Track the updated view event in Mixpanel
                        if ($viewerId !== self::LOGGED_OUT_VIEWER_ID) {
                            //if logged in
                            $mp->track('stream_view', [
                                'distinct_id' => $viewerId,
                                'stream_id' => $stream->id,
                                'stream_slug' => $stream->slug,
                            ]);
                            // increment user's "view count" by one
                            $mp->people->increment($viewerId, "stream view count", 1);
                        } else {
                            //if logged out
                            $mp->track('stream_view', [
                                'stream_id' => $stream->id,
                                'stream_slug' => $stream->slug,
                            ]);
                        }

                    }
                }
                // error_log('existing client with ' . $token);
            }
        }

    }

    private function recordViewPoint(string $viewerId, string $videoId, int $viewPoint): void
    {
        //this records where in the video was watched to
        VideoViewInfos::updateOrCreate(
            ['video_id' => $videoId],
            ['viewer_id' => $viewerId]
        )->update(['view_point' => $viewPoint]);
    }

    private function recordView(string $viewerId, string $videoId, string $sessionId, int $viewDuration): void
    {
        // Retrieve the view for the given viewer, video, and session
        $view = VideoViews::where([
            'session_id' => $sessionId,
            'video_id' => $videoId,
        ])->orWhere([
            'viewer_id' => $viewerId,
            'video_id' => $videoId,
        ])->first();
        // If the view does not exist, create a new view if the duration is under 2 minutes
        if ($view === null) {

            if ($viewDuration <= 120) {
                if ($viewerId !== self::LOGGED_OUT_VIEWER_ID) {
                    //create video view
                    $videoView = VideoViews::create([
                        'viewer_id' => $viewerId,
                        'video_id' => $videoId,
                        'session_id' => $sessionId,
                        'duration' => $viewDuration
                    ]);
                } else {
                    $videoView = VideoViews::create([
                        'video_id' => $videoId,
                        'session_id' => $sessionId,
                        'duration' => $viewDuration
                    ]);
                }
                $videoView->save();
            }
        } else {
            // If the view exists, update the duration if the difference between the new and old duration is under 20 seconds
            // and the total duration is under 2 minutes since the last update
            $lastUpdated = $view->updated_at;
            $durationDifference = $viewDuration - $view->duration;
            $currentTime = now();
            if ($durationDifference > 0 && $durationDifference <= 20 && $durationDifference <= $lastUpdated->addSeconds(120)->diffInSeconds($currentTime)) {
                $view->update(['duration' => $viewDuration]);
            }
        }
    }

    private function incrementViewCount(string $viewerId, LiveClient $liveClient): void
    {
        // Check if the view has already been counted for this connection
        if ($liveClient->view_counted === false) {
            $liveClient->view_counted = true;

            $this->video->increment('views', 1);
            $this->video->save();
            //this stops a view being count everytime a duration update is sent
            $liveClient->save();
            //get Mixpanel Instance
            $mp = app(MixPanelTrackingService::class)->mixPanel;
            // Track the updated view event in Mixpanel
            if ($viewerId !== self::LOGGED_OUT_VIEWER_ID) {
                //if logged in
                $mp->track('video_view', [
                    'distinct_id' => $viewerId,
                    'video_id' => $this->video->id,
                    'video_slug' => $this->video->slug,
                ]);
                // increment user's "view count" by one
                $mp->people->increment($viewerId, "view count", 1);
            } else {
                //if logged out
                $mp->track('video_view', [
                    'video_id' => $this->video->id,
                    'video_slug' => $this->video->slug,
                ]);
            }
        }
    }
}
