<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\PlaylistVideo;
use App\Models\Playlist;
use App\Models\Video;
use Auth;
use Illuminate\Http\Request;

class PlaylistVideoController extends Controller
{
    public function create(Request $request, $playlistId, $videoId)
    {


        if ($playlistId === "watch_later") {
            $playlistId = Auth::user()->creator->getServerMadePlaylist('Watch Later')->id;
        }

        // get playlist and video
        $playlist = Playlist::findOrFail($playlistId);
        $video = Video::findOrFail($videoId);

        // check both playlist and video exist
        if (!$playlist || !$video) {
            return response()->json([
                'error' => 'Playlist or video not found'
            ], 404);
        }

        // check if user is the owner of the playlist
        if ($playlist->owner->id !== Auth::user()->creator->id) {
            return response()->json([
                'error' => 'You do not have permission to add this video to the playlist',
                'playlist' => $playlist->owner()->id,
            ], 403);
        }

        // check if video is already in the playlist
        $existingPlaylistVideo = PlaylistVideo::where('playlist_id', $playlist->id)
            ->where('video_id', $video->id)
            ->first();

        // check if video is already in the playlist
        if ($existingPlaylistVideo) {
            return response()->json([
                'error' => 'Video already exists in playlist'
            ], 400);
        }
        // add video to playlist
        $playlistVideo = new PlaylistVideo();
        $playlistVideo->playlist_id = $playlist->id;
        $playlistVideo->video_id = $video->id;
        $playlistVideo->save();

        $playlist->video_count++;
        $playlist->recent_video_image = $video->thumbnail_url;
        $playlist->save();

        return response()->json([
            'success' => 'Video added to playlist'
        ], 200);
    }



    public function destroy(Request $request, $playlistId, $videoId)
    {
        if ($playlistId === "watch_later") {
            $playlistId = Auth::user()->creator->getServerMadePlaylist('Watch Later')->id;
        }

        // get if record exists
        $playlistVideo = PlaylistVideo::where('playlist_id', $playlistId)
            ->where('video_id', $videoId)
            ->firstOrFail();

        // if not found return 404
        if (!$playlistVideo) {
            return response()->json([
                'error' => 'Record not found in playlist'
            ], 404);
        }

        if ($playlistVideo->playlist->owner->id !== Auth::user()->creator->id) {
            return response()->json([
                'error' => 'You do not have permission to remove this video from the playlist'
            ], 403);
        }
        // delete record
        $playlistVideo->delete();

        // update playlist video count and recent video image
        $playlist = Playlist::findOrFail($playlistId);
        $playlist->video_count--;
        if($playlist->videos->first()) {
            $playlist->recent_video_image = $playlist->videos->first()->thumbnail_url;
        } else {
            $playlist->recent_video_image = null;
        }
        $playlist->save();

        return response()->json([
            'success' => 'Video removed from playlist'
        ], 200);
    }

}
