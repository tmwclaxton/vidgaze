<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\PlaylistModels\Playlist;
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


            $playlist->addVideo($videoId);

            $successCount++;


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

            // delete record
            $playlist->removeVideo($videoId);

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
