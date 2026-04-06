<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaceBook implements iSearchable, iIsPlatform
{
    private $apifyToken;

    public function __construct()
    {
        $this->apifyToken = env('APIFY_TOKEN');
    }

    public static function getPlatform(): Platform
    {
        return Platform::FaceBook;
    }


    public static function search(SearchQueryDTO $searchQuery): array
    {
        return [];
    }
}
