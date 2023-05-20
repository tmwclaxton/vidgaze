<?php

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\StreamCollection;
use App\Models\Category;
use App\Models\StreamModels\Stream;
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

    public function infinite(Request $request)
    {
        $perPage = $request->perPage ?? 20;
        $categoryIds = $request->categoryIds ?? [];

        if (!is_array($categoryIds)) {
            $categoryIds = explode(',', $categoryIds);
        }

        //return $categoryIds;
        $categories = new CategoryCollection(Category::query()->inRandomOrder()
            ->whereNotIn('id', $categoryIds)
            ->take($perPage)
            ->get());

        $result = [];

        foreach ($categories as $category) {
            $streams = new StreamCollection(Stream::query()
                ->where('category_id', $category->id)
                ->orderByDesc('viewers')
                ->take(6)
                ->get());

            $result[] = [
                'category' => $category,
                'streams' => $streams,
            ];
        }

        return $result;
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
