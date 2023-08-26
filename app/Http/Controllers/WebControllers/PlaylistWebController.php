<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Models\PlaylistModels\Playlist;
use App\Models\PlaylistModels\PlaylistVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use function Deployer\error;

class PlaylistWebController extends Controller
{
    public function show() {
        return Inertia::render('Viewer/Feed/Playlist/Playlist');
    }
}
