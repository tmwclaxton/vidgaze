<?php

namespace App\Http\Controllers\ApiControllers;


use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\StreamCollection;
use App\Http\Resources\StreamResource;
use App\Models\Category;
use App\Models\StreamModels\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Request;

class StreamApiController extends Controller
{


    /** get top streams
     * @return JsonResponse
     */
    public function topStreams()
    {
        $streams = new StreamCollection(
            Stream::orderBy('viewers', 'DESC')
                ->where('visibility', '=','public')
                ->where('streams.is_live', '=',true)
                ->take(6)->get() );
        return response()->json($streams);
    }






}
