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
        return Inertia::render('Viewer/Landing/Landing');
    }
    public function terms()
    {
        return Inertia::render('Legal/Terms');
    }
    public function privacy()
    {
        return Inertia::render('Legal/Policy');
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
