<?php

namespace App\Http\Controllers\ApiControllers;


use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\VideoModels\Video;
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

    public function grabVideoCategories(Request $request): JsonResponse
    {

        $query = Category::query();

        $query->where('twitch_category_id', '=', null);

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

    public function removeCategoryFromVideo(Request $request): \Illuminate\Http\JsonResponse
    {
        // grab video id
        $request->validate([
            'video_id' => 'required|integer|exists:videos,id',
        ]);

        $video = Video::find($request->video_id);

        // set category_id to null
        $video->category_id = null;

        $video->save();

        return response()->json([
            'message' => 'Category removed successfully'
        ]);
    }

    public function addCategoryToVideo(Request $request): \Illuminate\Http\JsonResponse
    {
        // grab video id, category id
        $request->validate([
            'video_id' => 'required|integer|exists:videos,id',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $video = Video::find($request->video_id);
        $category = Category::find($request->category_id);

        // set category_id to category id
        $video->category_id = $category->id;

        $video->save();

        return response()->json([
            'message' => 'Category added successfully'
        ]);
    }


}
