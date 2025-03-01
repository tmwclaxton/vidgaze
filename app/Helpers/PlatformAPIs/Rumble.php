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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Rumble implements iSearchable, iIsPlatform
{
    private $apifyToken;

    public function __construct()
    {
        $this->apifyToken = env('APIFY_TOKEN');
    }

    public static function getPlatform(): Platform
    {
        return Platform::Rumble;
    }

    public function getCreator(string $id): CreatorDTO
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apifyToken,
        ])->post('https://api.apify.com/v2/acts/azzouzana~rumble-all-inclusive-scraper/run-sync-get-dataset-items', [
            'startUrls' => ["https://rumble.com/c/$id"],
            'scrapeChannelVideos' => false,
            'scrapeChannelPlaylists' => false,
        ]);

        $data = $response->json();

        $creatorDTO = new CreatorDTO(Platform::Rumble, $id);
        $creatorDTO->name = $data['name'] ?? '';
        $creatorDTO->description = $data['description'] ?? '';
        $creatorDTO->avatar_url = $data['avatar_url'] ?? '';
        $creatorDTO->region = $data['region'] ?? '';

        return $creatorDTO;
    }

    public static function search(SearchQueryDTO $searchQueryDTO): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('APIFY_TOKEN'),
        ])->post('https://api.apify.com/v2/acts/azzouzana~rumble-all-inclusive-scraper/run-sync-get-dataset-items', [
            'startUrls' => ["https://rumble.com/search/all?q=" . urlencode($searchQueryDTO->query)],
        ]);

        $items = $response->json();

        return Arr::map($items, function ($value) {
            $resultDTO = new ResultDTO(Platform::Rumble, Kind::Video);
            $contentDTO = new ContentDTO(Platform::Rumble, Kind::Video, $value['id']);
            $creatorDTO = new CreatorDTO(Platform::Rumble, $value['creator_id']);

            $contentDTO->kind = Kind::Video;
            $contentDTO->publish_time = Carbon::make($value['publish_time']);
            $contentDTO->name = $value['name'];
            $contentDTO->duration = $value['duration'];
            $contentDTO->thumbnail_url = $value['thumbnail_url'];
            $contentDTO->tags = array_filter($value['tags'], function ($item) {
                return $item['name'] ?? false;
            });
            $contentDTO->description = $value['description'];
            $contentDTO->creator_id = $value['creator_id'];

            $creatorDTO->name = $value['creator_name'];
            $creatorDTO->description = $value['creator_description'] ?? "";
            $creatorDTO->avatar_url = $value['creator_avatar_url'];

            $resultDTO->content = $contentDTO;
            $resultDTO->creator = $creatorDTO;

            return $resultDTO;
        });
    }

    public static function getCreatorVideos(string $id, int $page = null, $maxResults = 100): array
    {
        if ($maxResults > 100) {
            throw new \Exception('Max results cannot be greater than 100');
        }

        $page = $page ?? 1;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('APIFY_TOKEN'),
        ])->post('https://api.apify.com/v2/acts/azzouzana~rumble-all-inclusive-scraper/run-sync-get-dataset-items', [
            'startUrls' => ["https://rumble.com/c/$id/videos?page=$page"],
            'scrapeChannelVideos' => true,
            'scrapeChannelPlaylists' => false,
        ]);

        $data = $response->json();

        if (!isset($data['data'])) {
            return [
                'next' => $page + 1,
                'hasNext' => false,
                'results' => [],
            ];
        }

        $results = array_map(function ($value) {
            $contentDTO = new ContentDTO(Platform::Rumble, Kind::Video, $value['id']);

            $contentDTO->kind = Kind::Video;
            $contentDTO->name = $value['name'];
            $contentDTO->duration = $value['duration'];
            $contentDTO->publish_time = Carbon::make($value['publish_time']);
            $contentDTO->thumbnail_url = $value['thumbnail_url'];
            $contentDTO->creator_id = $value['creator_id'];
            $contentDTO->tags = array_map(fn($item) => $item['name'], $value['tags']);

            return $contentDTO;
        }, $data['data']);

        return [
            'next' => $page + 1,
            'hasNext' => boolval($data['paging']['next']),
            'results' => $results,
        ];
    }
}
