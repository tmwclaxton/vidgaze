<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaylistResource;
use App\Http\Resources\VideoCollection;
use App\Models\PlaylistModels\Playlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistVideoApiController extends Controller
{

    private function checkForReservedPlaylist($playlistId)
    {

        switch ($playlistId) {
            case "watch_later":
                $playlistId = Auth::user()->creator->getServerMadePlaylist('Watch Later')->id;
                break;
            case "liked_videos":
                $playlistId = Auth::user()->creator->getServerMadePlaylist('Liked Videos')->id;
                break;
            case "history":
                $playlistId = Auth::user()->creator->getServerMadePlaylist('History')->id;
                break;
            case "disliked_videos":
                $playlistId = Auth::user()->creator->getServerMadePlaylist('Disliked Videos')->id;
                break;
        }

        return $playlistId;
    }

    /** index
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        $request->validate([
            'playlist_id' => 'required', // because we pass History, Watch Later, etc
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
        ]);
        $playlistId = $request->playlist_id;
        $page = $request->page ?? 1;
        $per_page = $request->per_page ?? 10;
        $playlistId = $this->checkForReservedPlaylist($playlistId);
        $playlist = Playlist::findOrFail($playlistId);
        // paginate in opposite order
        $videos = $playlist->videos()->paginate($per_page, ['*'], 'page', $page);
        return response()->json([
            'playlist' => new PlaylistResource($playlist),
            'videos' => $videos ? new VideoCollection($videos) : [],
        ]);

    }


    /** this creates a new playlist
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $request->validate([
            'playlist_id' => 'required', // because we pass History, Watch Later, etc
            'video_ids' => 'regex:/^[0-9,]+$/|required',
        ]);


        $playlistId = $request->playlist_id;
        $videoIds = explode(',', $request->video_ids);
        $playlistId = $this->checkForReservedPlaylist($playlistId);

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
                'error' => 'You do not have permission to add videos to this playlist'
            ], 403);
        }

        // add videos to playlist
        $successCount = 0;
        foreach ($videoIds as $videoId) {

            if ($playlist->addVideo(intval($videoId)))  {
                $successCount++;
            }

        }

        if ($successCount == 0) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'No videos added to ' . $playlist->name
            ], 200);
        }

        return response()->json([
            'toastType' => 'success',
            'message' => "$successCount videos added to " . $playlist->name
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

        $playlistId = $this->checkForReservedPlaylist($playlistId);

        // check if user is the owner of the playlist
        $playlist = Playlist::findOrFail($playlistId);
        if ($playlist->owner->id !== Auth::user()->creator->id) {
            return response()->json(
                [
                    'error' => 'You do not have permission to remove videos from this playlist'
                ], 403);

        }

        // remove each video from the playlist
        $successCount = 0;
        foreach ($videoIds as $videoId) {

            // delete record
            if ($playlist->removeVideo($videoId)) {
                $successCount++;
            }
        }

        if ($successCount == 0) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'No videos removed from ' . $playlist->name
            ], 200);
        }

        return response()->json([
            'toastType' => 'success',
            'message' => "$successCount videos removed from " . $playlist->name
        ], 200);
    }

}
