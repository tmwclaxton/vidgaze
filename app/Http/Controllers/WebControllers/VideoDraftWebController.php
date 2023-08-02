<?php

namespace App\Http\Controllers\WebControllers;

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

class VideoDraftWebController extends Controller
{

    public function upload(){
        return Inertia::render('Studio/Upload');
    }

    public function edit(string $slug)
    {
        $video = auth()->user()->creator()->first()->video_drafts()->where('slug', $slug)->firstOrFail();
        return Inertia::render('Studio/EditVideoDraft', [
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
        ]);
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
            if($publish_time->isPast()) return back()->withErrors(['publish_time' => 'Publish time must be in the future']);
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
        return redirect(route('studio.dashboard'))->with('success', 'Video draft updated');
    }

    public function publish(string $slug){
        $creator = auth()->user()->creator()->first();
        $videoDraft = $creator->video_drafts()->where('slug', $slug)->firstOrFail();

        $validated = request()->validate([
            'thumbnail' => ['file', 'required', 'mimes:jpeg,jpg,png', 'max:2048'],
            'title' => ['max:255', 'required', 'string', 'min:1'],
            'description' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'visibility' => ['required', 'string', 'in:public,unlisted,private,scheduled'],
            'language' => ['nullable', 'string'],
            'region' => ['nullable', 'string'],
            'audience' => ['required', 'string', 'in:all,kids,mature'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'publish_time' => ['nullable', 'int'],
            'platforms' => ['required', 'array', 'min:1', Rule::in($creator->getUploadablePlatforms())],
        ]);

        $publish_time = null;
        if(request()->visibility == Visibility::SCHEDULED->value){
            request()->validate([
                'publish_time' => ['required', 'int']
            ]);
            $publish_time = Carbon::createFromTimestamp(request()->publish_time);
            if($publish_time->isPast()) return back()->withErrors(['publish_time' => 'Publish time must be in the future']);
        }

        $thumbnail_path = null;
        if(request()->file('thumbnail'))
        {
            $thumbnail_path = request()->file('thumbnail')->storePublicly('thumbnails', 'public');
        }
        $video = Video::create([
            'slug' => $slug,
            'creator_id' => $creator->id,
            'preferred_source' => Platform::YouTube,
            'title' => request()->title,
            'description' => request()->description,
            'thumbnail_url' => \Storage::url($thumbnail_path),
            'tags' => json_encode(request()->tags),
            'visibility' => request()->visibility,
            'language' => request()->language,
            'region' => request()->region,
            'audience' => request()->audience,
            'category_id' => request()->category_id,
        ]);


        $uploadDTO = new UploadDTO(
            $video->id,
            $videoDraft->video_path,
            request()->title,
            request()->description,
            $creator->id,
            array_map(fn($platform) => Platform::fromValue($platform), request()->platforms),
            $thumbnail_path,
            request()->tags,
            Category::find(request()->category_id),
            Visibility::fromValue(request()->visibility),
            Audience::fromValue(request()->audience),
            $publish_time
        );
        $videoDraft->delete();

        $batch_id = Upload::platformUpload($creator->id, $video->id, $uploadDTO);

        return redirect(route('studio.dashboard'))->with('success', 'Video published');
    }
}
