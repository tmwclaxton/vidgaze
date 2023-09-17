<?php

namespace App\Http\Controllers\ApiControllers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Helpers\ImageCheck;
use App\Helpers\Upload;
use App\Helpers\UploadDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoDraftResource;
use App\Models\Category;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoDraft;
use Carbon\Carbon;
use GuzzleHttp\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use function GuzzleHttp\json_encode;

class VideoDraftApiController extends Controller
{


    public function edit(string $slug)
    {
        $video = auth()->user()->creator()->first()->video_drafts()->where('slug', $slug)->firstOrFail();
        $platforms = Platform::getUploadablePlatforms();
        // iterate through platforms and create an array of the platform and its capitalised name
        $platforms = $platforms->map(fn($platform) => [
            'value' => $platform->value,
            'name' => capitalisePlatformName($platform->value)
        ]);
        // check what platforms the creators has linked to their account

        return [

            'item' => new VideoDraftResource($video),
            'categories' => Category::where('youtube_category_id', '!=', null)
                ->orderBy('name')->get(['id', 'name'])->map(fn($category)=>
            [
                'value' => $category->id,
                'name' => $category->name
            ]),
            // for each platform, get the make array of the platform and its capitalised name
            'platforms' => $platforms
        ];
    }


    public function update(string $slug){
        $videoDraft = auth()->user()->creator()->first()->video_drafts()->where('slug', $slug)->firstOrFail();
        request()->validate([
            //'image' => ['nullable', 'image', 'max:10240', 'dimensions:min_width=640,min_height=360,ratio=16/9'],
            'title' => ['max:100', 'required', 'string', 'min:1'],
            'description' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'array'],
            'visibility' => ['nullable', 'string', 'in:public,unlisted,private,scheduled'],
            'language' => ['nullable', 'string'],
            'region' => ['nullable', 'string'],
            'audience' => ['nullable', 'string', 'in:all,kids,mature'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'publish_time' => ['nullable', 'required_if:visibility,scheduled', 'date'],
            'platforms' => ['nullable', 'array', Rule::in(Platform::getUploadablePlatforms(false))],
            'preferred_source' => ['nullable', 'string', Rule::in(Platform::getUploadablePlatforms(false)), 'required_with:platforms', Rule::in(request()->platforms)],
        ]);

        $publish_time = null;
        if(request()->visibility == Visibility::SCHEDULED->value){
            if (!request()->publish_time) return  response()->json(['errors' => [ 'publish_time' => ['Publish time is required']]], 422);
            $publish_time = request()->publish_time; // 2023-09-29T11:51:00.000Z
            $publish_time = Carbon::parse($publish_time);
            if($publish_time < Carbon::now()){
                return  response()->json(['errors' => [ 'publish_time' => ['Publish time must be in the future']]], 422);
            }
        }

        $videoDraft->update([
            'title' => request()->title,
            'description' => request()->description,
            'tags' => request()->tags ? Utils::jsonEncode(request()->tags) : null,
            'visibility' => request()->visibility,
            'language' => request()->language,
            'region' => request()->region,
            'audience' => request()->audience,
            'category_id' => request()->category_id,
            'publish_time' => $publish_time ?? null,
            'platforms' =>request()->platforms ?  Utils::jsonEncode(request()->platforms) : null,
            'preferred_source' => request()->preferred_source,
        ]);
        $videoDraft->save();

        return response()->json([
            'draft' => new VideoDraftResource($videoDraft),
        ]);
    }


    public function updateThumbnail(Request $request, string $slug) {
        //grab the video draft
        $videoDraft = auth()->user()->creator()->first()->video_drafts()->where('slug', $slug)->firstOrFail();
        //$thumbnail_path = null;
        //if(request()->hasFile('image') && ImageCheck::inapropriateImageCheck(request()->file('image'))){
        //    $thumbnail_path = request()->file('image')->storePublicly('thumbnails', 'public');
        //}

        if ($request->hasFile('image')) {
            if (ImageCheck::inapropriateImageCheck($request->file('image'))) {
                return [
                    'toastType' => 'warning',
                    'message' => 'This image is inappropriate. Please upload another image.'
                ];
            }

            if ($videoDraft->thumbnail_path && Storage::exists($videoDraft->thumbnail_path)) {
                Storage::delete($videoDraft->thumbnail_path);
            }

            // store the image and get the path that is available to the public
            $url = Storage::url($request->file('image')->store('public/thumbnails'));
        } else {
            $url = null;
        }

        // update the video draft with the new thumbnail path
        $videoDraft->update([
            'thumbnail_path' => $url
        ]);


        return [
            'toastType' => 'success',
            'message' => 'Thumbnail updated successfully.'
        ];
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
            if($publish_time->isPast()) return  response()->json(['errors' => [ 'publish_time' => ['Publish time must be in the future']]], 422);
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
            'time_published' => $publish_time,
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

        return response()->json([
            'batch_id' => $batch_id,
            'video_slug' => $video->slug
        ]);
    }


    public function destroy(string $slug)
    {

        $creator = auth()->user()->creator()->first();
        $videoDraft = $creator->video_drafts()->where('slug', $slug)->firstOrFail();
        // find thumbnail path and delete it
        if ($videoDraft->thumbnail_path && Storage::exists($videoDraft->thumbnail_path)) {
            Storage::delete($videoDraft->thumbnail_path);
        }
        // find video path and delete it
        if ($videoDraft->video_path && Storage::exists($videoDraft->video_path)) {
            Storage::delete($videoDraft->video_path);
        }
        $videoDraft->delete();
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
            // TODO:  make this only accessible to the creator
            $url = Storage::url(\request()->file('video')->store('public/videos'));
            VideoDraft::where('slug', $slug)->update(['video_path' => $url]);
            return response()->json();
        }
        catch (\Exception $e){
            // delete the video draft if there is an error
            VideoDraft::where('slug', $slug)->delete();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


}
