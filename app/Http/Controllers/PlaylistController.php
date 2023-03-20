<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\Playlist;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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


    public function index()
    {
        return view('/feed/library', [
            'playlists' => Playlist::query()->where([
                ['creator_id', '=', Auth::user()->creator->id,],['visibility', '!=', 'hidden']
            ])
                ->orderBy('server_made', 'DESC')
                ->orderBy('video_count', 'DESC')
                ->orderBy('updated_at','DESC')
                ->get(),
            'history' => Auth::user()->creator->getPlaylist('History',true)->videos->reverse()->skip(0)->take(4),
            'later' => Auth::user()->creator->getPlaylist('Watch Later',true)->videos->reverse()->skip(0)->take(4),
            'liked' => Auth::user()->creator->getPlaylist('Liked Videos',true)->videos->reverse()->skip(0)->take(4),
        ]);
    }
    public function later()
    {
        $playlist = Auth::user()->creator->getPlaylist('Watch Later',true);
        return $this->getView($playlist, false);

    }

    public function liked()
    {
        $playlist = Auth::user()->creator->getPlaylist('Liked Videos',true);
        return $this->getView($playlist, false);

    }
    public function history()
    {
        $playlist = Auth::user()->creator->getPlaylist('History',true);
        return $this->getView($playlist, false);

    }

    public function show(Playlist $playlist) {
        if (isset(Auth::user()->creator)) {
            return $this->getView($playlist, (($playlist->owner->id == Auth::user()->creator->id) && !$playlist->server_made));
        } else {
            abort(401);
        }
    }

    public function getView($playlist, $editable) {

        //forbidden if visibility is set to private and you don't own it
        if ($playlist->visibility == 'private' && $playlist->creator_id != Auth::user()->creator->id) {
            abort(401);
        }

        //refresh video count
        $playlist->video_count = $playlist->videos->count();
        $playlist->save();
        //get view
        return view('/feed/playlist', [
            'playlist' => $playlist,
            'editable' => $editable,
            'videos' => $playlist->videos->reverse()->values(),
        ]);
    }


    public function store() {

    }

    public function edit()
    {
//        return view('studio/customise');
    }

    public function update(Playlist $playlist )
    {
        if ($playlist->owner->id == Auth::user()->creator->id && !$playlist->server_made) {
            request()->validate([
                'name' => 'max:100|min:3',
                'visibility' => 'in:private,public,unlisted',
                'delete' => ''
                //            'id' => 'required|exists:playlists,id'
            ]);
            $attributes = request()->all();
            if (isset($attributes['name'])) {
                $playlist->update([
                    'name' => $attributes['name'],
                ]);
            }
            if (isset($attributes['visibility'])) {
                $playlist->update([
                    'visibility' => $attributes['visibility'],
                ]);
            }
            if (isset($attributes['delete'])) {
                $playlist->delete();
                return Redirect::to('/feed/library');

            }
        }
        return back();
    }

    public function destroy()
    {
        //
    }
}
