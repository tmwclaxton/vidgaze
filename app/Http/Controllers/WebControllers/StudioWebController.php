<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class StudioWebController extends Controller
{
    public function content()
    {
        return Inertia::render('Studio/Content/Content');
    }

    public function dashboard()
    {
        return Inertia::render('Studio/Dashboard/Dashboard');
    }

    public function stream()
    {
        return Inertia::render('Studio/Stream/Stream');
    }

    public function customise()
    {
        return Inertia::render('Studio/Customise/Customise');
    }
}
