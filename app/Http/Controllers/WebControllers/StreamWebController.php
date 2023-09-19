<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\StreamCollection;
use App\Http\Resources\StreamResource;
use App\Models\Category;
use App\Models\StreamModels\Stream;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StreamWebController extends Controller
{

    public function index()
    {


        return Inertia::render('Viewer/Streams/StreamsIndex');

    }

    public function show()
    {
        return Inertia::render('Viewer/Watch/Watch', ['type' => "stream"]);
    }

    public function edit(string $slug)
    {
        return Inertia::render('Studio/StudioEditItem/StudioEditItem', [
            'slug' => $slug,
            'type' => 'stream'
        ]);
    }

}
