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


    public function playlist_modal_refresh(Request $request)
    {
        $playlists = Playlist::query()->where([
            ['creator_id', '=', Auth::user()->creator->id],
            ['visibility', '!=', 'hidden'],
            ['name', '!=', 'Liked Videos'],
            ['name', '!=', 'History'],

        ])->orderBy('server_made', 'DESC')
            ->orderBy('updated_at','DESC')
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

        return ['playlists' => $playlists];
    }

}
