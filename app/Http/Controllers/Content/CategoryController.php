<?php

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->perPage ?? 20;
        $categories = new CategoryCollection(Category::query()
            ->where('thumbnail_url', '!=', null)
            ->where('tags_json', '!=', null)
            ->where('twitch_category_id', '!=', null)
            ->inRandomOrder()
            ->take($perPage)
            ->get());
        return $categories;
    }


    public function create()
    {
        //
    }


    public function store()
    {

    }

    public function show(Category $category) {
//        dd($category->id);
        return view('category', [
                'category' => $category,
            ]
        );
    }


    public function edit()
    {
    }


    public function update()
    {

    }


    public function destroy()
    {
        //
    }
}
