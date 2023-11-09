<?php

namespace App\Http\Controllers\ApiControllers;


use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\StreamCollection;
use App\Http\Resources\StreamResource;
use App\Models\Category;
use App\Models\StreamModels\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StreamApiController extends Controller
{


    /** get top streams
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        // validate the request ie per_page
        $request->validate([
            'per_page' => 'integer',
            'skip' => 'integer',
            'category_id' => 'integer|exists:categories,id',
        ]);
        // max no is 100, default is 20
        $per_page = $request->per_page ?? 20;
        $per_page = $per_page > 50 ? 50 : $per_page;
        $category_id = $request->category_id ?? null;
        $skip = $request->skip ?? 0;

        // grab streams
        $streamQuery = Stream::with('creator')
                ->where('visibility', '=','public')
                ->where('streams.is_live', '=',true)
            ->orderBy('viewers', 'desc');

        if ($category_id) {
            $streamQuery->where('category_id', '=', $category_id);
        }

        $streams = $streamQuery
            ->skip($skip)
            ->take($per_page)
            ->get();

        // return the streams
        $streams = new StreamCollection($streams);

        return response()->json([
            'results' => $streams->count(),
            'streams' => $streams,
        ]);



    }


    public function show(string $slug) {
        // grab the stream
        $stream = Stream::with('creator')
            ->where('slug', '=', $slug)
            ->firstOrFail();

        // if the stream is private and the user is not the owner
        if ($stream->visibility === 'private' && $stream->creator->id !== Auth::id()) {
            // return forbidden
            abort(403);
        }

        // return the stream
        return [
            'stream' => new StreamResource($stream)
        ];
    }




}
