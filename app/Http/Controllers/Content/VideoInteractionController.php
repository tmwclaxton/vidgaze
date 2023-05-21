<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\PlaylistModels\Playlist;
use App\Models\PlaylistModels\PlaylistVideo;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoInteractionController extends Controller
{
    public function index()
    {
    }
    private function getVideoAndInteraction($videoId)
    {
        $video = Video::findOrFail($videoId);

        if (!$video) {
            return response()->json([
                'error' => 'Video not found'
            ], 404);
        }

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
    public function getVideoInteraction($videoId) {
        // check if user is authenticated
        if (!Auth::user()) {
            return response()->json([
                'error' => 'You are not authenticated'
            ], 401);
        }

        $creatorId = Auth::user()->creator->id;
        // check if user has liked video
        $VideoViewInfo = VideoInteraction::where('viewer_id', $creatorId)->where('video_id', $videoId)->first() ?? null;
        return [
            'liked' => $VideoViewInfo['liked'] ?? null,
            'reported' => $VideoViewInfo['reported'] ?? null,
            'disinterested' => $VideoViewInfo['disinterested'] ?? null,
            'view_point' => $VideoViewInfo['view_point'] ?? null,
        ];
    }


    public function toggleDisinterest(Request $request, $videoId)
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
            'type' => $type,
            'message' => $message,
        ]);
    }

    public function toggleReport(Request $request, $videoId)
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
            'message' => $message,
            'type' => $type,
        ]);
    }



    public function toggleLike($videoId)
    {
        //return $videoId;
        $video = Video::findOrFail($videoId);

        if (!isset(Auth::user()->creator)) {
            return response()->json(['message' => 'You are not authenticated.'], 401);
        }

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
            'message' => $message,
            'type' => $type,
            'result' => $interaction->liked,
        ], 200);
    }

    public function toggleDislike($videoId)
    {
        $video = Video::findOrFail($videoId);

        if (!isset(Auth::user()->creator)) {
            return response()->json(['message' => 'You are not authenticated.'], 401);
        }
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
            'message' => $message,
            'type' => $type,
            'result' => $interaction->liked,
        ], 200);
    }

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


    // this is for the video modal
    public function modalDetails($videoId)
    {
        [$video, $interaction] = $this->getVideoAndInteraction($videoId);

        // check if video exists
        if (!$video) {
            return response()->json([
                'error' => 'Video not found'
            ], 404);
        }

        // check if user is authenticated
        if (!Auth::user()) {
            return response()->json([
                'error' => 'You are not authenticated'
            ], 401);
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
