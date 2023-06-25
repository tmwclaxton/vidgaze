<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Google;
use App\Helpers\PlatformAPIs\Podcasts;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use Google_Service_YouTube;
use Laravel\Octane\Facades\Octane;

class JoshPing
{

    public static function ping()
    {

        dd(auth()->user()->creator->sources()->get('source_name')->toArray());

        return dd('pong');
    }
}
