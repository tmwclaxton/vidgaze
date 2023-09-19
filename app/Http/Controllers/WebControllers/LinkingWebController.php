<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class LinkingWebController extends Controller
{
    public function link(string $platform)
    {
        return Inertia::render('Studio/StudioLinkRedirect');
    }
}
