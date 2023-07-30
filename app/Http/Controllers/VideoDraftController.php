<?php

namespace App\Http\Controllers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Helpers\Upload;
use App\Helpers\UploadDTO;
use App\Http\Controllers\Upload\UploadController;
use App\Models\Category;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoDraft;
use Illuminate\Support\Facades\Bus;
use Inertia\Inertia;

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
        $path = \request()->file('video')->store('videos');
        VideoDraft::where('slug', $slug)->update(['video_url' => $path]);
        return response()->json();
    }

    public function edit(string $slug)
    {
        $video = auth()->user()->creator()->first()->video_drafts()->where('slug', $slug)->firstOrFail();
        return Inertia::render('Studio/EditVideoDraft', [
           'video' => [
               'slug' => $video->slug,
               'title' => $video->title,
               'description' => $video->description,
               'tags' => $video->tags,
               'visibility' => $video->visibility,
               'language' => $video->language,
               'region' => $video->region,
               'audience' => $video->audience,
               'category' => $video->category_id,
               'platforms' => ['youtube', 'dailymotion', 'vimeo'],
               'usePublishTime'=> false,
               'thumbnail' => $video->thumbnail_url,
           ]
        ]);
    }

    public function update(string $slug){
        ddd(request()->all());

        $videoDraft = auth()->user()->creator()->first()->video_drafts()->where('slug', $slug)->firstOrFail();
        $validated = request()->validate([
            'video_url' => ['nullable'],
            'thumbnail' => ['nullable'],
            'title' => ['nullable'],
            'description' => ['nullable'],
            'tags' => ['nullable'],
            'visibility' => ['nullable'],
            'language' => ['nullable'],
            'region' => ['nullable'],
            'audience' => ['nullable'],
            'category_id' => ['nullable', 'integer'],
            'creator_id' => ['required', 'integer'],
        ]);
        $videoDraft->update($validated);
        return response()->json();
    }

    public function publish(string $slug){
        $creator = auth()->user()->creator()->first();
        $videoDraft = $creator->video_drafts()->where('slug', $slug)->firstOrFail();

        $validated = request()->validate([
            'thumbnail' => ['file', 'required'],
            'title' => ['max:255', 'required', 'string', 'min:1'],
            'description' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'visibility' => ['required', 'string'],
            'language' => ['nullable', 'string'],
            'region' => ['nullable', 'string'],
            'audience' => ['required', 'string'],
            'category' => ['required', 'integer'],
        ]);

        $thumbnail_path = request()->file('thumbnail')->store('thumbnails');

        $cat = new Category();
        $cat->youtube_category_id = 22;
        $uploadDTO = new UploadDTO(
            $video_path,
            request()->title,
            request()->description,
            $creator->id,
            [Platform::YouTube],
            $thumbnail_path,
            request()->tags,
            $cat,
            Visibility::fromValue(request()->visibility),
            Audience::fromValue(request()->audience)
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
