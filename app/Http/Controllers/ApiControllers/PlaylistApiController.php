<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaylistCollection;
use App\Http\Resources\PlaylistResource;
use App\Http\Resources\VideoCollection;
use App\Models\PlaylistModels\Playlist;
use App\Models\PlaylistModels\PlaylistVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use function Deployer\error;

class PlaylistApiController extends Controller
{
    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item

    /** get playlist by slug
     * @return JsonResponse
     */
    public function show($request) {
        $request->validate([
            'slug' => 'required',
        ]);
        $slug = $request->slug;

        // if not logged in and playlist is private or hidden
        if (!Auth::check()) {
            $extraWheres = [
                ['visibility', '=', 'public']
            ];
        } else {
            $extraWheres = [
                ['visibility', '!=', 'hidden'],
                ['creator_id', '=', Auth::user()->creator->id]
            ];
        }

        $where = [
            ['slug', '=', $slug],
        ];

        $where = array_merge($where, $extraWheres);

        $playlist = Playlist::where(
            $where
        )->first();

        if (!$playlist) {
            return response()->json([
                'toastType' => 'error',
                'message' => 'Playlist not found.'
            ]);
        }

        return response()->json([
            'playlist' => new PlaylistResource($playlist),
        ]);
    }

    /** create a playlist
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $request->validate([
            // if name isn't Watch Later, History, Liked Videos or Disliked Videos
            'name' => 'required|max:100|min:3|not_in:Watch Later,History,Liked Videos,Disliked Videos',
            'visibility' => 'required|in:public,private,unlisted'
        ]);

        $playlist = Playlist::create([
            'name' => $request->name,
            'visibility' => $request->visibility,
            'creator_id' => Auth::user()->creator->id,
            'server_made' => false,
            'slug' => uniqid()
        ]);

        return response()->json([
            'toastType' => 'success',
            'message' => 'Playlist created successfully.',
            'playlist' => new PlaylistResource($playlist),
        ]);
    }

    /** update a playlist
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request) {
        $request->validate([
            'name' => 'required|max:100|min:3',
            'visibility' => 'required|in:public,private,unlisted',
            'playlist_id' => 'required|exists:playlists,id|integer'
        ]);

        $playlist = Playlist::where([
            ['creator_id', '=', Auth::user()->creator->id],
            ['id', '=', $request->playlist_id],
            ['server_made', '=', '0']
        ])->first();

        if (!$playlist) {
            return response()->json([
                'toastType' => 'error',
                'message' => 'Playlist not found.'
            ]);
        }

        $playlist->name = $request->name;
        $playlist->visibility = $request->visibility;
        $playlist->save();

        return response()->json([
            'toastType' => 'success',
            'message' => 'Playlist updated successfully.',
            'playlist' => new PlaylistResource($playlist),
        ]);
    }

    /** delete a playlist
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request) {
        $request->validate([
            'playlist_id' => 'required|exists:playlists,id|integer'
        ]);

        $playlist = Playlist::where([
            ['creator_id', '=', Auth::user()->creator->id],
            ['id', '=', $request->playlist_id],
            ['server_made', '=', '0']
        ])->first();


        if (!$playlist) {
            return response()->json([
                'toastType' => 'error',
                'message' => 'Playlist not found.'
            ]);
        }

        $playlist->delete();

        return response()->json([
            'toastType' => 'success',
            'message' => 'Playlist deleted successfully.',
        ]);
    }


    /** refresh the playlist modal
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'video_ids' => 'nullable|string',
            'where' => 'in:all,modal|nullable'
        ]);

        $method = $request->where;
        $wheres = [
            ['creator_id', '=', Auth::user()->creator->id],
            ['visibility', '!=', 'hidden'],
        ];
        if ($method == 'modal') {
            $wheres = array_merge($wheres, [
                ['name', '!=', 'Liked Videos'],
                ['name', '!=', 'History'],
            ]);
        }
        $playlists = Playlist::query()->where($wheres)->orderByDesc('updated_at')->get();
        $playlists = new PlaylistCollection($playlists);

        if ($method === 'modal') {
            $video_ids = explode(',', $request->video_ids);
            foreach ($playlists as $playlist) {
                $playlist->videos_present_in_playlist = false; // default value

                foreach ($video_ids as $video_id) {
                    $playlist_video = PlaylistVideo::where([
                        ['playlist_id', '=', $playlist->id],
                        ['video_id', '=', $video_id],
                    ])->first();

                    if ($playlist_video) {
                        $playlist->videos_present_in_playlist = true;
                        break; // video found, no need to continue searching
                    }
                }
            }
            //order playlists by server_made then videos_present_in_playlist then updated_at
            // can't cause computed order doesn't work or something
            $playlists = $playlists->where('videos_present_in_playlist' , true)->merge($playlists->where('videos_present_in_playlist' , false));
        }

        //put server made playlists at the top
        $playlists = $playlists->where('server_made' , true)->merge($playlists->where('server_made' , false));

        return response()->json(['playlists' => $playlists]);

    }

}
