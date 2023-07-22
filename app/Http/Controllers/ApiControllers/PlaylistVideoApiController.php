<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\PlaylistModels\Playlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistVideoApiController extends Controller
{

    /** this creates a new playlist
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $playlistId = $request->playlist_id;
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
                'toastType' => 'warning',
                'toastMessage' => 'No videos added to playlist'
            ], 200);
        }

        return response()->json([
            'toastType' => 'success',
            'toastMessage' => "$successCount videos added to playlist"
        ], 200);
    }


    /** this deletes a playlist
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        $playlistId = $request->playlist_id;
        $videoIds = explode(',', $request->video_ids);

        if ($playlistId === "watch_later") {
            $playlistId = Auth::user()->creator->getServerMadePlaylist('Watch Later')->id;
        }

        // check if user is the owner of the playlist
        $playlist = Playlist::findOrFail($playlistId);
        if ($playlist->owner->id !== Auth::user()->creator->id) {
            return response()->json(
                [
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
                'toastType' => 'warning',
                'toastMessage' => 'No videos removed from playlist'
            ], 200);
        }

        return response()->json([
            'toastType' => 'success',
            'toastMessage' => "$successCount videos removed from playlist"
        ], 200);
    }

}
