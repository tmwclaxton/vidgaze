<?php

namespace App\Helpers\PlatformAPIs;

interface iPlatfromSearch
{
    public static function search($searchQuery, int $maxResults = 20, $pageToken = null,  $filters = null);
}
