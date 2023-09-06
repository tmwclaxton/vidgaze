<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreatorRequest;
use App\Http\Requests\UpdateCreatorRequest;
use App\Http\Resources\CreatorCollection;
use App\Models\CreatorModels\Creator;
use App\Models\PodcastModels\Podcast;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CreatorWebController extends Controller
{
    public function show($slug)
    {
        return Inertia::render('Viewer/Channel/Channel', [
            'slug' => $slug,
        ]);
    }
}
