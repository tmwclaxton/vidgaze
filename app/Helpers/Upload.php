<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Bus;
use App\Jobs\UploadPlatform;


class Upload
{

    public static function upload(string $directory, $request){
        $path = $request->file($directory)->store('videos');
        return $path;
    }

    public static function platformUpload(int $creator_id, int $video_id, UploadDTO $uploadDTO)
    {
        $upload_jobs = [];
        foreach ($uploadDTO->platforms as $platform) {
            $upload_jobs[] = new UploadPlatform($creator_id, $video_id, $uploadDTO, $platform);
        }
        $batch = Bus::batch($upload_jobs)->onQueue('upload')->onConnection('redis')->dispatch();
        return $batch->id;
    }
}
