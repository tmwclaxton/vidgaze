<?php

namespace App\Http\Controllers\WebControllers;


use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\StreamCollection;
use App\Models\Category;
use App\Models\StreamModels\Stream;
use Illuminate\Http\Request;

class CategoryWebController extends Controller
{
    public function index() {
        return inertia('Viewer/Category/Categories', [
            'meta' => \App\Helpers\Seo::make('Categories', 'Browse videos by topic and country on VidGaze.'),
        ]);
    }
    public function show($slug) {
        return inertia('Viewer/Category/Category', [
            'slug' => $slug,
            'meta' => \App\Helpers\Seo::category($slug),
        ]);
    }




}
