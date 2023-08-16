<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class VideoDraftWebController extends Controller
{

    public function upload(){
        return Inertia::render('Studio/Upload');
    }

    public function edit(string $slug)
    {
        return Inertia::render('Studio/EditVideoDraft', [
            'slug' => $slug,
        ]);
    }
}
