<?php

namespace App\Helpers\PlatformAPIs;

use App\Helpers\SearchQueryDTO;

interface iSearchable
{
    public static function search(SearchQueryDTO $searchQuery);
}
