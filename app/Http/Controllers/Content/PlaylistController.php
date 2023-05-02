<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\PlaylistVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use function Deployer\error;

class PlaylistController extends Controller
{
    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100|min:3',
            'visibility' => 'required'
        ]);
        Playlist::create([
            'name' => $request->name,
            'visibility' => $request->visibility,
            'creator_id' => Auth::user()->creator->id,
            'server_made' => false,
            'slug' => uniqid(),
        ]);
        return Redirect::back()->with('success', 'Playlist created successfully.');
    }


    public function playlist_modal_refresh(Request $request)
    {
        $playlists = Playlist::query()->where([
            ['creator_id', '=', Auth::user()->creator->id],
            ['visibility', '!=', 'hidden'],
            ['name', '!=', 'Liked Videos'],
            ['name', '!=', 'History'],

        ])->orderByDesc('updated_at')
            ->get();

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

        //put server made playlists at the top
        $playlists = $playlists->where('server_made' , true)->merge($playlists->where('server_made' , false));

        return ['playlists' => $playlists];

    }

}
