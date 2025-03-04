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
    public function grabStreamCategories(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100',
            // category_ids is a comma separated list of category ids of numbers and commas
            'category_ids' => 'string|nullable|regex:/^([0-9]+,)*[0-9]+$/',
            'ensure_details' => 'boolean|nullable',
        ]);
        $per_page = $request->per_page ?? 20;

        $query = Category::query();

        if ($request->ensure_details) {
            $query->where('thumbnail_url', '!=', null)
                ->where('tags_json', '!=', null)
                ->where('twitch_category_id', '!=', null);
        }

        if ($request->category_ids != null) {
            $query->whereNotIn('id', explode(',', $request->category_ids));
        }

        $query->inRandomOrder()
            ->take($per_page);

        $categories = $query
            ->get();

        if (empty($categories)) {
            return response()->json([
                'message' => 'No categories found',
            ], 404);
        }

        return response()->json([
            'categories' => new CategoryCollection($categories),
        ]);
    }

    public function grabVideoCategories(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100',
            // category_ids is a comma separated list of category ids of numbers and commas
            'category_ids' => 'string|nullable|regex:/^([0-9]+,)*[0-9]+$/',
            'ensure_details' => 'boolean|nullable',
        ]);
        $per_page = $request->per_page ?? 20;

        $query = Category::query();

        if ($request->ensure_details) {
            $query->where('thumbnail_url', '!=', null)
                ->where('tags_json', '!=', null)
                ->where('twitch_category_id', '!=', null);
        }

        if ($request->category_ids != null) {
            $query->whereNotIn('id', explode(',', $request->category_ids));
        }

        $query->inRandomOrder()
            ->take($per_page);

        $categories = $query
            ->get();

        if (empty($categories)) {
            return response()->json([
                'message' => 'No categories found',
            ], 404);
        }

        return response()->json([
            'categories' => new CategoryCollection($categories),
        ]);
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
