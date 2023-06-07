<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;

class SearchQueryDTO
{

    public string $query;
    private array $platforms;
    public Kind $kind;
    public string $region;
    public string $language;
    public string $category;

    public int $max_results;
    public function __construct(string $query, int $max_results = 20, array $platforms = null)
    {
        if(!isset($platforms)){
            $platforms = Platform::getSupportedPlatforms(true);
        }
        $this->query = $query;
        $this->max_results = $max_results;
        $this->setPlatforms($platforms);
    }

    public function getPlatforms(): array
    {
        return $this->platforms;
    }

    public function setPlatforms(array $platforms): void
    {
        // filter out unsupported platforms
        $supported_platforms = Platform::getSupportedPlatforms(true)->toArray();
        $this->platforms = array_filter($platforms, function ($platform) use ($supported_platforms) {
            return in_array($platform, $supported_platforms);
        });
    }
}
