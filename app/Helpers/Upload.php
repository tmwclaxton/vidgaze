<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Bus;
use App\Jobs\UploadPlatform;


class Upload
{
    public static function upload(int $creator_id, UploadDTO $uploadDTO)
    {
        $upload_jobs = [];
        foreach ($uploadDTO->platforms as $platform) {
            $upload_jobs[] = new UploadPlatform($creator_id, $uploadDTO, $platform);
//            UploadPlatform::dispatchSync($creator_id, $uploadDTO, $platform);
//            UploadPlatform::dispatch($creator_id, $uploadDTO, $platform)->onQueue('upload')->onConnection('redis');
        }
        $batch = Bus::batch($upload_jobs)->onQueue('upload')->onConnection('redis')->dispatch();
        return $batch->id;
    }
}
