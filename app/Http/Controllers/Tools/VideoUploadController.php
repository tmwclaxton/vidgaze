<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\VideoModels\VideoUpload;
use Illuminate\Support\Facades\Auth;

class VideoUploadController extends Controller
{
    public function show() {
        return view('studio/upload', [
            'video_upload'=> Auth::user()->creator->video_upload,
        ]);
    }

    public function upload() {
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
//            'privacy_Status' => ['required',new Enum(PrivacyStatus::class)],
//            'publish_At' => ['required'],
//            'platforms' => ['required'],
//            //'time_zone' =>  [Rule::requiredIf('privacy_Status' == PrivacyStatus::scheduled->name)],
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
