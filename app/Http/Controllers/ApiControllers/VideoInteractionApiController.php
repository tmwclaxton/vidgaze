<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\PlaylistModels\Playlist;
use App\Models\PlaylistModels\PlaylistVideo;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoInteractionApiController extends Controller
{
    /** get the video interaction and the liked playlist
     * @param $id
     * @return array
     */
    private function fetchVideoInteractionAndLikedPlaylist($id)
    {
        $interaction = VideoInteraction::where('video_id', $id)
            ->where('viewer_id', Auth::user()->creator->id)
            ->first();

        if (!$interaction) {
            $interaction = new VideoInteraction();
            $interaction->video_id = $id;
            $interaction->viewer_id = Auth::user()->creator->id;
            $interaction->save();
        }

        $likedPlaylist = Auth::user()->creator->getServerMadePlaylist('Liked Videos');


        return [$interaction, $likedPlaylist];
    }



    /** get the video model and the interaction model
     * @param $videoId
     * @return array
     */
    private function getVideoAndInteraction($videoId)
    {
        $video = Video::findOrFail($videoId);

        $creatorId = Auth::user()->creator->id;

        $interaction = VideoInteraction::where([
            'viewer_id' => $creatorId,
            'video_id' => $video->id,
        ])->first();

        if (!$interaction) {
            $interaction = new VideoInteraction();
            $interaction->viewer_id = $creatorId;
            $interaction->video_id = $video->id;
            $interaction->save();
        }

        return [$video, $interaction];
    }

    /** get the video interaction
     * @param $videoId
     * @return JsonResponse
     */
    public function getVideoInteraction($videoId) {
        $creatorId = Auth::user()->creator->id;
        // check if user has liked video
        $VideoViewInfo = VideoInteraction::where('viewer_id', $creatorId)->where('video_id', $videoId)->first() ?? null;
        return response()->json([
            'liked' => $VideoViewInfo['liked'] ?? null,
            'reported' => $VideoViewInfo['reported'] ?? null,
            'disinterested' => $VideoViewInfo['disinterested'] ?? null,
            'view_point' => $VideoViewInfo['view_point'] ?? null,
        ]);
    }

    /** toggled disinterested
     * @param $videoId
     * @return JsonResponse
     */
    public function toggleDisinterest($videoId)
    {
        [$video, $interaction] = $this->getVideoAndInteraction($videoId);
        $interaction->disinterested = !$interaction->disinterested;
        $interaction->save();
        if ($interaction->disinterested) {
            $type = 'normal';
            $message = 'Got it, we will show you less videos like this';
        } else {
            $type = 'undo';
            $message =  'Got it, we will show you more videos like this';
        }
        return response()->json([
            'toastType' => $type,
            'message' => $message,
        ]);
    }

    /** toggle video report
     * @param $videoId
     * @return JsonResponse
     */
    public function toggleReport($videoId)
    {
        [$video, $interaction] = $this->getVideoAndInteraction($videoId);
        $interaction->reported = !$interaction->reported;
        if ($interaction->reported) {
            $video->increment('report_count');
            $type = 'normal';
            $message = 'Thank you for reporting this video. We will review it as soon as possible.';
        } else {
            $video->decrement('report_count');
            $type = 'undo';
            $message = 'Report removed successfully.';
        }
        $interaction->save();

        return response()->json([
            'toastType' => $type,
            'message' => $message,
        ]);
    }



    /** toggle like
     * @param $videoId
     * @return JsonResponse
     */
    public function toggleLike($videoId)
    {
        //return $videoId;
        $video = Video::findOrFail($videoId);

        [$interaction, $likedPlaylist] = $this->fetchVideoInteractionAndLikedPlaylist($videoId);

        //if they change their rating from dislike to like
        $likedPlaylist->addVideo($video->id);
        $message = 'Liked successfully';

        if ($interaction->liked == 'dislike') {
            $video->dislike_count--;
        }

        if ($interaction->liked != 'like') {
            $interaction->liked = 'like';
            $type = 'normal';
            $video->like_count++;
        } else {
            // Have they already liked it
            $interaction->liked = null;
            $type = 'undo';
            $video->like_count--;
            $likedPlaylist->removeVideo($video->id);
            $message = 'Like removed successfully';
        }

        $interaction->save();
        $video->save();
        return response()->json([
            'toastType' => $type,
            'message' => $message,
            'result' => $interaction->liked,
        ]);
    }

    /** toggle dislike
     * @param $videoId
     * @return JsonResponse
     */
    public function toggleDislike($videoId)
    {
        $video = Video::findOrFail($videoId);

        $message = 'Disliked successfully';

        [$interaction, $likedPlaylist] = $this->fetchVideoInteractionAndLikedPlaylist($videoId);

        //if they change their rating from like to dislike
        if ($interaction->liked == 'like') {
            $likedPlaylist->removeVideo($video->id);
            $video->like_count--;
        }

        if ($interaction->liked != 'dislike') {
            $interaction->liked = 'dislike';
            $type = 'normal';
            $video->dislike_count++;
            $liked = 'dislike';
        } else {
            // Have they already disliked it
            $interaction->liked = null;
            $type = 'undo';
            $video->dislike_count--;
            $liked = null;
            $message = 'Dislike removed successfully';
        }

        $interaction->save();
        $video->save();
        return response()->json([
            'toastType' => $type,
            'message' => $message,
            'result' => $interaction->liked,
        ], 200);
    }



    /** this is for the video modal
     * @param $videoId
     * @return JsonResponse
     */
    public function modalDetails($videoId)
    {
        [$video, $interaction] = $this->getVideoAndInteraction($videoId);

        // check if video exists
        if (!$video) {
            return response()->json([
                'error' => 'Video not found'
            ], 404);
        }


        $creatorId = Auth::user()->creator->id;

        // check if video is in watch later playlist
        $inWatchLater = false;
        if ($watchLaterPlaylist = Playlist::where('name', 'Watch Later')->where('creator_id', $creatorId)->first()) {
            if (PlaylistVideo::where('playlist_id', $watchLaterPlaylist->id)->where('video_id', $videoId)->first()) {
                $inWatchLater = true;
            }
        }

        // check if user has disinterested video
        $videoDisinterested = (bool)$interaction->disinterested;
        $videoReported = (bool)$interaction->reported;

        // check if user has disinterested channel
        $channelDisinterested = CreatorInteraction::where('viewer_id', $creatorId)->where('creator_id', $video->creator->id)->where('disinterested', '=', true)->exists();



        return response()->json([
            'inWatchLater' => $inWatchLater,
            'videoDisinterest' => $videoDisinterested,
            'reportedContent' => $videoReported,
            'channelDisinterest' => $channelDisinterested,
        ], 200);
    }


}
