<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Platform;

interface iIsPlatform
{
    public static function getPlatform(): Platform;
}
