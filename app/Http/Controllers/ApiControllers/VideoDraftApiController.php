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
    public function getEdit(string $slug)
    {
        $video = auth()->user()->creator()->first()->video_drafts()->where('slug', $slug)->firstOrFail();
        return [
            'video' => [
                'slug' => $video->slug,
                'title' => $video->title,
                'description' => $video->description,
                'tags' => json_decode($video->tags)?? [],
                'visibility' => $video->visibility,
                'language' => $video->language,
                'region' => $video->region,
                'audience' => $video->audience,
                'category_id' => $video->category_id,
                'platforms' => json_decode($video->platforms)?? [],
                'publish_time' => $video->publish_time ? Carbon::create($video->publish_time)->timestamp: null,
                'thumbnail' => $video->thumbnail_path,
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name'])->map(fn($category)=>
            [
                'value' => $category->id,
                'name' => $category->name
            ]),
        ];
    }



    public function update(string $slug){

        $videoDraft = auth()->user()->creator()->first()->video_drafts()->where('slug', $slug)->firstOrFail();
        request()->validate([
            'thumbnail' => ['file', 'nullable'],
            'title' => ['max:255', 'required', 'string', 'min:1'],
            'description' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'visibility' => ['nullable', 'string', 'in:public,unlisted,private,scheduled'],
            'language' => ['nullable', 'string'],
            'region' => ['nullable', 'string'],
            'audience' => ['nullable', 'string', 'in:all,kids,mature'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'publish_time' => ['nullable', 'int'],
            'platforms' => ['nullable', 'array', 'min:1', Rule::in(Platform::getUploadablePlatforms(false))],
        ]);


        $publish_time = null;
        if(request()->visibility == Visibility::SCHEDULED->value){
            request()->validate([
                'publish_time' => ['required', 'int']
            ]);
            $publish_time = Carbon::createFromTimestamp(request()->publish_time);
            if($publish_time->isPast()) return  response()->json(['errors' => [ 'publish_time' => ['Publish time must be in the future']]], 422);
        }

        $thumbnail_path = null;
        if(request()->file('thumbnail'))
        {
            $thumbnail_path = request()->file('thumbnail')->storePublicly('thumbnails', 'public');
        }
        $videoDraft->update([
            'thumbnail_path' => $thumbnail_path,
            'title' => request()->title,
            'description' => request()->description,
            'tags' => request()->tags ? json_encode(request()->tags) : null,
            'visibility' => request()->visibility,
            'language' => request()->language,
            'region' => request()->region,
            'audience' => request()->audience,
            'category_id' => request()->category_id,
            'publish_time' => $publish_time ?? null,
            'platforms' =>request()->platforms ?  json_encode(request()->platforms) : null
        ]);

        return response()->json();
    }


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
