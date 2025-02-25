<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Audience;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\Tools;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class YouTube implements iSearchable, iIsPlatform
{
    protected $apifyClient;
    protected $apifyApiKey;

    public function __construct()
    {
        $this->apifyApiKey = env('APIFY_API_KEY');
        $this->apifyClient = new Client([
            'base_uri' => 'https://api.apify.com/v2/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apifyApiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }

    public static function getPlatform(): Platform
    {
        return Platform::YouTube;
    }

    public static function getCreators(array $ids): array
    {
        $self = new self();
        $creators = [];
        foreach ($ids as $id) {
            $response = $self->apifyClient->request('GET', "acts/apify~youtube-scraper/runs/last/dataset/items", [
                'query' => ['query' => "channel:$id"],
            ]);
            $data = json_decode($response->getBody(), true);
            if (!empty($data)) {
                $creators[] = self::extractCreatorToDTO($data[0]);
            }
        }
        return $creators;
    }

    public static function search(SearchQueryDTO $searchQueryDTO)
    {
        $self = new self();
        $response = $self->apifyClient->request('GET', "acts/apify~youtube-scraper/runs/last/dataset/items", [
            'query' => ['query' => $searchQueryDTO->query],
        ]);
        $data = json_decode($response->getBody(), true);

        $results = [];
        foreach ($data as $item) {
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Video);
            $contentDTO = new ContentDTO(
                Platform::YouTube,
                Kind::Video,
                $item['id']
            );
            $contentDTO->creator_id = $item['channelId'];
            $contentDTO->name = $item['title'];
            $contentDTO->description = $item['description'];
            $contentDTO->duration = Tools::convertYouTubeDurationToSeconds($item['duration']);
            $contentDTO->tags = $item['tags'] ?? [];
            $contentDTO->publish_time = Carbon::parse($item['date']);
            $contentDTO->thumbnail_url = $item['thumbnailUrl'];
            $resultDTO->content = $contentDTO;
            $results[] = $resultDTO;
        }
        return $results;
    }

    public static function getVideoOrStream(array $ids, bool $returnJustContentDTO = true): array
    {
        $self = new self();
        $videos = [];
        foreach ($ids as $id) {
            $response = $self->apifyClient->request('GET', "acts/apify~youtube-scraper/runs/last/dataset/items", [
                'query' => ['query' => "video:$id"],
            ]);
            $data = json_decode($response->getBody(), true);
            if (!empty($data)) {
                $videos[] = $data[0];
            }
        }

        return array_map(function ($video) use ($returnJustContentDTO) {
            $kind = ($video['isLive'] ?? false) ? Kind::Stream : Kind::Video;

            $resultDTO = new ResultDTO(Platform::YouTube, $kind);
            $contentDTO = new ContentDTO(
                Platform::YouTube,
                $kind,
                $video['id']
            );

            $contentDTO->creator_id = $video['channelId'];
            $contentDTO->name = $video['title'];
            $contentDTO->description = $video['description'];
            $contentDTO->duration = Tools::convertYouTubeDurationToSeconds($video['duration']);
            $contentDTO->tags = $video['tags'] ?? [];
            $contentDTO->publish_time = Carbon::parse($video['date']);
            $contentDTO->thumbnail_url = $video['thumbnailUrl'];

            if ($kind === Kind::Stream) {
                $contentDTO->is_live = true;
            }

            $resultDTO->content = $contentDTO;

            if ($returnJustContentDTO) return $contentDTO;

            $resultDTO->creator = new CreatorDTO(Platform::YouTube, $video['channelId']);
            return $resultDTO;
        }, $videos);
    }

    public static function extractCreatorToDTO(array $data): CreatorDTO
    {
        $creatorDTO = new CreatorDTO(Platform::YouTube, $data['channelId']);
        $creatorDTO->name = $data['channelName'];
        $creatorDTO->avatar_url = $data['thumbnailUrl'];
        $creatorDTO->banner_url = $data['bannerUrl'] ?? null;
        $creatorDTO->description = $data['channelDescription'];
        $creatorDTO->region = $data['location'] ?? null;
        $creatorDTO->language = $data['defaultLanguage'] ?? null;
        return $creatorDTO;
    }

    public static function getCreatorVideosBeforeDate(string $id, Carbon $date = null, $maxResults = 50, bool $includeStreams = true, bool $onlyStreams = false): array
    {
        $self = new self();
        $query = "channel:$id";
        if ($date) {
            $query .= " date:$date->toDateString()";
        }

        $response = $self->apifyClient->request('GET', "acts/apify~youtube-scraper/runs/last/dataset/items", [
            'query' => ['query' => $query, 'maxResults' => $maxResults],
        ]);
        $data = json_decode($response->getBody(), true);

        // Filter based on streams
        $filteredData = array_filter($data, function ($item) use ($includeStreams, $onlyStreams) {
            $isStream = $item['isLive'] ?? false;
            if ($onlyStreams) {
                return $isStream;
            }
            if (!$includeStreams) {
                return !$isStream;
            }
            return true;
        });

        // Map to ResultDTO
        $results = array_map(function ($item) {
            $kind = ($item['isLive'] ?? false) ? Kind::Stream : Kind::Video;

            $resultDTO = new ResultDTO(Platform::YouTube, $kind);
            $contentDTO = new ContentDTO(
                Platform::YouTube,
                $kind,
                $item['id']
            );
            $contentDTO->creator_id = $item['channelId'];
            $contentDTO->name = $item['title'];
            $contentDTO->description = $item['description'];
            $contentDTO->duration = Tools::convertYouTubeDurationToSeconds($item['duration']);
            $contentDTO->tags = $item['tags'] ?? [];
            $contentDTO->publish_time = Carbon::parse($item['date']);
            $contentDTO->thumbnail_url = $item['thumbnailUrl'];

            $resultDTO->content = $contentDTO;
            return $resultDTO;
        }, $filteredData);

        return [
            'next' => $data ? Carbon::parse(end($data)['date']) : null,
            'hasNext' => count($data) === $maxResults,
            'results' => $results,
        ];
    }

    public static function getAllCreatorVideos(string $id): array
    {
        $hasNext = true;
        $lastPublishedAt = null;
        $results = [];
        while ($hasNext) {
            $content = self::getCreatorVideosBeforeDate($id, $lastPublishedAt);
            $results = array_unique(array_merge($results, $content['results']), SORT_REGULAR);
            $lastPublishedAt = $content['next'];
            $hasNext = $content['hasNext'];
        }
        return $results;
    }
}
