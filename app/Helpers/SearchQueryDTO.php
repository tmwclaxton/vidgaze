<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Support\PlatformRegistry;

class SearchQueryDTO
{
    public string $query;

    private array $platforms;

    public Kind $kind = Kind::Video;

    public string $region = 'US';

    public string $language = '';

    public string $category = '';

    public int $max_results;

    public function __construct(string $query, int $max_results = 20, ?array $platforms = null)
    {
        if (! isset($platforms)) {
            $platforms = PlatformRegistry::unifiedSearchPlatforms()->all();
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
        $supported_platforms = PlatformRegistry::unifiedSearchPlatforms()->all();
        $this->platforms = array_values(array_filter($platforms, function ($platform) use ($supported_platforms) {
            return in_array($platform, $supported_platforms, true);
        }));
    }

    public function getStoreCountryCode(): string
    {
        $r = strtoupper(trim($this->region));

        return strlen($r) === 2 ? $r : 'US';
    }
}
