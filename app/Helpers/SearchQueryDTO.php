<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;

class SearchQueryDTO
{

    public string $query;
    public Platform $platform;
    public Kind $kind;
    public string $region;
    public string $language;
    public string $category;

    public int $max_results;
    public function __construct(string $query, int $max_results = 20)
    {
        $this->query = $query;
        $this->max_results = $max_results;
    }
}
