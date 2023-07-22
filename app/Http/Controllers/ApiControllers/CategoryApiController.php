<?php

namespace App\Http\Controllers\ApiControllers;


use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\StreamCollection;
use App\Models\Category;
use App\Models\StreamModels\Stream;
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

    /** returns categories along with streams for the infinite scroll
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategoriesWithStreams(Request $request)
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

        return response()->json($result);
    }




}
