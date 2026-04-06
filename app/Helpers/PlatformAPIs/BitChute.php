<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\SearchQueryDTO;

class BitChute implements iIsPlatform, iSearchable
{
    public static function getPlatform(): Platform
    {
        return Platform::BitChute;
    }

    public static function search(SearchQueryDTO $searchQuery): array
    {
        return [];
    }
}
