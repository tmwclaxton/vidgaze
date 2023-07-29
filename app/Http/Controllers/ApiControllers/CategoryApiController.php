<?php

namespace App\Http\Controllers\ApiControllers;


use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{


    /** returns categories for the home page
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'perPage' => 'integer|min:1|max:100',
        ]);
        $perPage = $request->perPage ?? 20;
        $categories = new CategoryCollection(Category::query()
            ->where('thumbnail_url', '!=', null)
            ->where('tags_json', '!=', null)
            ->where('twitch_category_id', '!=', null)
            ->inRandomOrder()
            ->take($perPage)
            ->get());
        return response()->json($categories);
    }

    public function show($slug) {
        // grab the stream
        $category = Category::query()->where('slug', '=', $slug)->firstOrFail();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }


        // return the category
        return response()->json([
            'category' => new CategoryResource($category),
        ]);

    }



}
