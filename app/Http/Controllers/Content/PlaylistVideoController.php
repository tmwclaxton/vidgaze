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
    public function create(Request $request, $playlistId)
    {
        $videoIds = explode(',', $request->video_ids);

        if ($playlistId === "watch_later") {
            $playlistId = Auth::user()->creator->getServerMadePlaylist('Watch Later')->id;
        }

        // get playlist
        $playlist = Playlist::findOrFail($playlistId);

        // check playlist exists
        if (!$playlist) {
            return response()->json([
                'error' => 'Playlist not found'
            ], 404);
        }

        // check if user is the owner of the playlist
        if ($playlist->owner->id !== Auth::user()->creator->id) {
            return response()->json([
                'error' => 'You do not have permission to add videos to the playlist'
            ], 403);
        }

        // add videos to playlist
        $successCount = 0;
        foreach ($videoIds as $videoId) {
            $video = Video::find($videoId);

            // check if video exists
            if (!$video) {
                continue;
            }

            // check if video is already in the playlist
            $existingPlaylistVideo = PlaylistVideo::where('playlist_id', $playlist->id)
                ->where('video_id', $video->id)
                ->first();

            if (!$existingPlaylistVideo) {
                // add video to playlist
                $playlistVideo = new PlaylistVideo();
                $playlistVideo->playlist_id = $playlist->id;
                $playlistVideo->video_id = $video->id;
                $playlistVideo->save();

                $playlist->video_count++;
                $playlist->recent_video_image = $video->thumbnail_url;
                $playlist->save();

                $successCount++;
            }
        }

        if ($successCount == 0) {
            return response()->json([
                'error' => 'No videos added to playlist'
            ], 200);
        }

        return response()->json([
            'success' => "$successCount videos added to playlist"
        ], 200);
    }
    public function destroy(Request $request, $playlistId)
    {
        $videoIds = explode(',', $request->video_ids);

        if ($playlistId === "watch_later") {
            $playlistId = Auth::user()->creator->getServerMadePlaylist('Watch Later')->id;
        }

        // check if user is the owner of the playlist
        $playlist = Playlist::findOrFail($playlistId);
        if ($playlist->owner->id !== Auth::user()->creator->id) {
            return response()->json([
                'error' => 'You do not have permission to remove videos from the playlist'
            ], 403);
        }

        // remove each video from the playlist
        $successCount = 0;
        foreach ($videoIds as $videoId) {
            // get if record exists
            $playlistVideo = PlaylistVideo::where('playlist_id', $playlistId)
                ->where('video_id', $videoId)
                ->first();

            // if not found continue with next video
            if (!$playlistVideo) {
                continue;
            }

            // delete record
            $playlistVideo->delete();

            // update playlist video count and recent video image
            $playlist->video_count--;
            if($playlist->videos->first()) {
                $playlist->recent_video_image = $playlist->videos->first()->thumbnail_url;
            } else {
                $playlist->recent_video_image = null;
            }
            $playlist->save();

            $successCount++;
        }

        if ($successCount == 0) {
            return response()->json([
                'error' => 'No videos removed from playlist'
            ], 200);
        }

        return response()->json([
            'success' => "$successCount videos removed from playlist"
        ], 200);
    }

}
