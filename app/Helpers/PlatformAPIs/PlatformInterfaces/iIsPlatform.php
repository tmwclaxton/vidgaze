<?php

namespace App\Helpers\PlatformAPIs\PlatformInterfaces;

use App\Enums\Platform;

interface iIsPlatform
{
    public static function getPlatform(): Platform;
}
