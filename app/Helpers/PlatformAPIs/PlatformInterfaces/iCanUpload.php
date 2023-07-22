<?php

namespace App\Helpers\PlatformAPIs\PlatformInterfaces;

use App\Helpers\UploadDTO;

interface iCanUpload
{
    public function upload(UploadDTO $uploadDTO): string;

}
