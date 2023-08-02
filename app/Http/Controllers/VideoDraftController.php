<?php

namespace App\Http\Controllers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Helpers\Upload;
use App\Helpers\UploadDTO;
use App\Models\Category;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoDraft;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Inertia\Inertia;
use function GuzzleHttp\json_encode;

class VideoDraftController extends Controller
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
            $path = \request()->file('video')->store('videos');
            VideoDraft::where('slug', $slug)->update(['video_url' => $path]);
            return response()->json();
        }
        catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
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
                'use_publish_time'=> $video->use_publish_time == 1,
                'publish_time' => $video->publish_time ? Carbon::create($video->publish_time)->timestamp: null,
                'thumbnail' => $video->thumbnail_url,
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
            'visibility' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'region' => ['nullable', 'string'],
            'audience' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'publish_time' => ['nullable', 'int'],
            'platforms' => ['nullable', 'array'],
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
        if(request()->file('thumbnail')) $thumbnail_path = request()->file('thumbnail')->store('thumbnails');
        $videoDraft->update([
            'thumbnail_url' => $thumbnail_path,
            'title' => request()->title,
            'description' => request()->description,
            'tags' => request()->tags ? json_encode(request()->tags) : null,
            'visibility' => request()->visibility,
            'language' => request()->language,
            'region' => request()->region,
            'audience' => request()->audience,
            'category_id' => request()->category_id,
            'publish_time' => $publish_time ?? null,
            'use_publish_time' => request()->use_publish_time,
            'platforms' =>request()->platforms ?  json_encode(request()->platforms) : null
        ]);
        return redirect(route('studio.dashboard'))->with('success', 'Video draft updated');
    }

    public function publish(string $slug){
        $creator = auth()->user()->creator()->first();
        $videoDraft = $creator->video_drafts()->where('slug', $slug)->firstOrFail();

        $validated = request()->validate([
            'thumbnail' => ['file', 'required'],
            'title' => ['max:255', 'required', 'string', 'min:1'],
            'description' => ['nullable', 'string'],
            'tags' => ['required', 'array'],
            'tags.*' => ['string'],
            'visibility' => ['required', 'string'],
            'language' => ['nullable', 'string'],
            'region' => ['nullable', 'string'],
            'audience' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'publish_time' => ['nullable', 'int'],
            'platforms' => ['required', 'array'],
        ]);

        $publish_time = null;
        if(request()->visibility == Visibility::SCHEDULED->value){
            request()->validate([
                'publish_time' => ['required', 'int']
            ]);
            $publish_time = Carbon::createFromTimestamp(request()->publish_time);
            if($publish_time->isPast()) return back()->withErrors(['publish_time' => 'Publish time must be in the future']);
        }

        $thumbnail_path = request()->file('thumbnail')->store('thumbnails');

        $uploadDTO = new UploadDTO(
            $video_path,
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

        $video = Video::create([
            'slug' => $slug,
            'creator_id' => $creator->id,
            'preferred_source' => Platform::YouTube,
            'title' => request()->title,
            'description' => request()->description,
            'thumbnail_url' => '$thumbnail_path',
        ]);
        $batch_id = Upload::platformUpload($creator->id, $video->id, $uploadDTO);

        $batch = Bus::findBatch($batch_id);

        while(!$batch->finished()) {
            sleep(1);
            $batch = Bus::findBatch($batch_id);
        }

        dd("done");

    }
}
