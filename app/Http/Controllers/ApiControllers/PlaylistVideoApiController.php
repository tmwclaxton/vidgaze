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

    private function checkForReservedPlaylist($playlist_slug)
    {

        switch ($playlist_slug) {
            case "watch_later":
                $playlist_slug = Auth::user()->creator->getServerMadePlaylist('Watch Later')->slug;
                break;
            case "liked_videos":
                $playlist_slug = Auth::user()->creator->getServerMadePlaylist('Liked Videos')->slug;
                break;
            case "history":
                $playlist_slug = Auth::user()->creator->getServerMadePlaylist('History')->slug;
                break;
            case "disliked_videos":
                $playlist_slug = Auth::user()->creator->getServerMadePlaylist('Disliked Videos')->slug;
                break;
            default:
                break;
        }

        return $playlist_slug;
    }

    /** index
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        $request->validate([
            'playlist_slug' => 'required',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
        ]);
        $playlist_slug = $request->playlist_slug;
        $page = $request->page ?? 1;
        $per_page = $request->per_page ?? 10;
        $playlist_slug = $this->checkForReservedPlaylist($playlist_slug);
        $playlist = Playlist::where([
            ['slug', '=', $playlist_slug],
            ['creator_id', '=', Auth::user()->creator->id]
        ])->first();
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
            'playlist_slug' => 'required', // because we pass History, Watch Later, etc
            'video_ids' => 'regex:/^[0-9,]+$/|required',
        ]);


        $playlist_slug = $request->playlist_slug;
        $videoIds = explode(',', $request->video_ids);
        $playlist_slug = $this->checkForReservedPlaylist($playlist_slug);

        // get playlist
        $playlist = Playlist::where([
            ['slug', '=', $playlist_slug],
            ['creator_id', '=', Auth::user()->creator->id]
        ])->first();

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
        $playlist_slug = $request->playlist_slug;
        $videoIds = explode(',', $request->video_ids);

        $playlist_slug = $this->checkForReservedPlaylist($playlist_slug);

        // check if user is the owner of the playlist
        $playlist = Playlist::where('slug', $playlist_slug)->firstOrFail();
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
