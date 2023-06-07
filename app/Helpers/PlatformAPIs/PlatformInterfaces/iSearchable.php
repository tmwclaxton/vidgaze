<?php

namespace App\Helpers\PlatformAPIs\PlatformInterfaces;

use App\Helpers\SearchQueryDTO;

interface iSearchable
{
    public static function search(SearchQueryDTO $searchQuery);
}
