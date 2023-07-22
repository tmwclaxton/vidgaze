<?php

namespace App\Http\Controllers\Tools;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Helpers\PlatformAPIs\YouTube;
use App\Helpers\Upload;
use App\Helpers\UploadDTO;
use App\Http\Controllers\Controller;
use App\Jobs\UploadPlatform;
use App\Models\Category;
use App\Models\VideoModels\VideoUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Inertia\Inertia;

class VideoUploadController extends Controller
{
    public function show() {
        return Inertia::render('Studio/Upload');
    }

    public function upload() {
        $creator = auth()->user()->creator()->first();

        $video_path = request()->file('video')->store('videos');
        $thumbnail_path = request()->file('thumbnail')->store('thumbnails');

        $tags = explode(',', request()->tags);

        $cat = new Category();
        $cat->youtube_category_id = 22;
        $uploadDTO = new UploadDTO(
            $video_path,
            request()->title,
            request()->description,
            $creator->id,
            [Platform::YouTube],
            $thumbnail_path,
            $tags,
            $cat,
            Visibility::UNLISTED,
            Audience::ALL
        );

        $batch_id = Upload::upload($creator->id, $uploadDTO);

        $batch = Bus::findBatch($batch_id);

        while(!$batch->finished()) {
            sleep(1);
            $batch = Bus::findBatch($batch_id);
        }

        dd("done");

        //validate video
        $attribute = request()->validate([

            'video' => 'required|mimetypes:video/,video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi|max:32000000000', // 32 GB Max

        ]);
        //create record
        $video_upload = VideoUpload::firstOrCreate(['creator_id' => Auth::user()->creator->id]); //this doesn't check video slugs prob should

        //delete old file if it exists
        if($video_upload->local_video_url != null) {
            if (file_exists( public_path().$video_upload->local_video_url))  {
                unlink(public_path().$video_upload->local_video_url);
            }
        }

        //create video name time adds a bit of uniqueness so you can't guess it easily
        $videoName = time() . '-' .  Auth::User()->creator->id . '-' . 'video' . '.' . $attribute['video']->extension();

        //create the file if this doesn't work run php artisan storage:link
        $attribute['video']->storePubliclyAs('videos', $videoName); //remember to symlink

        //add file loc to video upload model
        $video_upload->update([
            'local_video_url'=> '/storage/videos/'. $videoName ,
            'reserved_video_slug' => generateRandomString(16),
            'platforms' => ['','','','','']]); //can;t have default values for slugs for jsons

        return redirect('/studio/upload');

    }
    //TODO
    // check validation with other platforms
    // publish_At regex format validation and timezone

    public function upload2(){
        //return view(dd(request('tags')));
//
//        $attributes = request()->validate([
//            'title' => ['required','string','max:100'],//"regex:^([^<>]*)$"], //exclude < >
//            'description' => ['string','max:5000','nullable'],//,'regex:^([^<>]*)$'],
//            'video_tmp' => ['required','string'],
//            'thumbnail_path' => ['required','string'],
//            'thumbnail_MIME' => ['required','string'],
//            'made_for_kids' => ['required','bool'],
//            'tags' => ['array','nullable'],
//            'category_id' => ['required', Rule::exists('categories', 'id')],
//            //'thumbnail' => 'required|image',
//            'privacy_Status' => ['required',new Enum(Visibility::class)],
//            'publish_At' => ['required'],
//            'platforms' => ['required'],
//            //'time_zone' =>  [Rule::requiredIf('privacy_Status' == Visibility::scheduled->name)],
//        ]);
//
//        //$attributes['user_id'] = auth()->id();
//        //$attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails', 'public');
//
//       //Post::create($attributes);
//
//        return redirect('/');
    }

}
