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
        return inertia('Viewer/Category/Categories', []);
    }
    public function show($slug) {
        return inertia('Viewer/Category/Category', [
            'slug' => $slug,
        ]);
    }




}
