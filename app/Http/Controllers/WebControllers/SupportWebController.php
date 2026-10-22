<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Mail\SupportMail;
use App\Models\Award;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SupportWebController extends Controller
{
    public function home()
    {
        return Inertia::render('Viewer/Home/Homepage');
    }
    public function about()
    {
        return Inertia::render('Viewer/Landing/Landing', [
            'meta' => \App\Helpers\Seo::make('About', 'VidGaze is an open-source video platform: one place to watch creators from YouTube, Rumble and more, ranked by the community.'),
        ]);
    }
    public function terms()
    {
        return Inertia::render('Legal/Terms', [
            'meta' => \App\Helpers\Seo::make('Terms of Service', 'The terms for using VidGaze, a video aggregator that embeds public videos from other platforms.'),
        ]);
    }
    public function privacy()
    {
        return Inertia::render('Legal/Policy', [
            'meta' => \App\Helpers\Seo::make('Privacy Policy', 'How VidGaze collects, uses and protects your personal data, and the cookies it uses.'),
        ]);
    }
    public function support()
    {
        return Inertia::render('Legal/Support');
    }
    public function marketplace()
    {
        return Inertia::render('Marketplace/Marketplace', [
            'products' => Product::all(),
            'awards' => Award::all()->sortByDesc('coin_price')->take(3)
        ]);
    }
}
