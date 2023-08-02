<?php

namespace App\Http\Controllers\ApiControllers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Helpers\Upload;
use App\Helpers\UploadDTO;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoDraft;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use function GuzzleHttp\json_encode;

class VideoDraftApiController extends Controller
{

    public function index()
    {
        return auth()->user()->creator()->videoDrafts()->all();
    }
    public function show(string $slug)
    {
        return auth()->user()->creator()->videoDrafts()->where('slug', $slug)->firstOrFail();
    }

//    public function update(string $slug)
//    {
//        request()->validate([
//            'video_url' => ['nullable'],
//            'thumbnail_url' => ['nullable'],
//            'title' => ['nullable'],
//            'description' => ['nullable'],
//            'tags' => ['nullable'],
//            'visibility' => ['nullable'],
//            'language' => ['nullable'],
//            'region' => ['nullable'],
//            'audience' => ['nullable'],
//            'category_id' => ['nullable', 'integer'],
//            'creator_id' => ['required', 'integer'],
//            'slug' => ['required'],
//        ]);
//
//        return auth()->user()->creator()->videoDrafts()->where('slug', $slug)->firstOrFail()
//            ->update(request()->validated());
//    }

    public function destroy(string $slug)
    {
        auth()->user()->creator()->videoDrafts()->where('slug', $slug)->firstOrFail()->delete();
        return response()->json();
    }

    public function primeNewVideoDraft()
    {
        return response()->json([
            'slug' => VideoDraft::create([
                'slug' => generateRandomString(16),
                'creator_id' => auth()->user()->creator()->first()->id
            ])->slug
        ]);
    }

    public function upload(string $slug){
        try {
            request()->validate([
                'video' => ['required', 'file', 'mimes:mp4,mov'],
            ]);
            $path = \request()->file('video')->store('videos');
            VideoDraft::where('slug', $slug)->update(['video_path' => $path ]);
            return response()->json();
        }
        catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
