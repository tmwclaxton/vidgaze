<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
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


    public function playlist_modal_refresh()
    {

        $playlists = Playlist::query()->where([
            ['creator_id', '=', Auth::user()->creator->id],
            ['visibility', '!=', 'hidden'],
            ['name', '!=', 'Liked Videos'],
            ['name', '!=', 'History'],

        ])->orderBy('server_made', 'DESC')
            ->orderBy('updated_at','DESC')
            ->get();
        return ['playlists' => $playlists];
    }
}
