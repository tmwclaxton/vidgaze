<?php

namespace App\Http\Controllers\Content;

use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Models\channelDisinterest;
use App\Models\Playlist;
use App\Models\PlaylistVideo;
use App\Models\Video;
use App\Models\videoDisinterest;
use App\Models\videoReport;
use App\Models\VideoViewInfos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use function Deployer\Support\array_merge_alternate;

class VideoController extends Controller
{
    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item

    public function index()
    {
        return Inertia::render('Viewer/Videos/VideosIndex');
    }


    // this is for the video modal
    public function details(Request $request, $videoId)
    {
        $video = Video::findOrFail($videoId);

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
        $videoDisinterest = VideoDisinterest::where('creator_id', $creatorId)->where('video_id', $videoId)->exists();

        // check if user has disinterested channel
        $channelDisinterest = ChannelDisinterest::where('creator_id', $creatorId)->where('channel_id', $video->creator->id)->exists();

        // check if user has reported video
        $reportVideo = VideoReport::where('creator_id', $creatorId)->where('video_id', $videoId)->exists();

        return response()->json([
            'inWatchLater' => $inWatchLater,
            'videoDisinterest' => $videoDisinterest,
            'channelDisinterest' => $channelDisinterest,
            'reportVideo' => $reportVideo,
        ], 200);
    }


}
