<?php

namespace App\Helpers\PlatformAPIs;

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
use Illuminate\Support\Facades\Http;
use Laravel\Octane\Facades\Octane;

class YouTube implements iSearchable, iIsPlatform
{
    protected string $scraperKey;

    public function __construct()
    {
        $this->scraperKey = env('SCRAPER_KEY'); // Retrieve the Scraper-Key from the environment
    }

    public static function getPlatform(): Platform
    {
        return Platform::YouTube;
    }

    public static function getCreators(array $ids): array
    {
        $yt = new self();
        if (!$ids) return [];
        if (count($ids) > 50) {
            throw new \Exception('Too many ids, max 50');
        }

        $tasks = array_map(function ($id) use ($yt) {
            return function () use ($id, $yt) {
                $response = Http::withHeaders([
                    'Scraper-Key' => $yt->scraperKey,
                ])->get("https://api.scraper.tech/channel.php?channel_id=$id");
                if ($response->successful()) {
                    return $response->json()['data'];
                } else {
                    return null;
                }
            };
        }, $ids);

        $responses = Octane::concurrently($tasks, 10000);

        $creators = [];
        foreach ($responses as $response) {
            if ($response) {
                $creators[] = self::extractCreatorToDTO($response);
            }
        }

        return $creators;
    }

    public static function search(SearchQueryDTO $searchQueryDTO)
    {
        $yt = new self();

        $resultDTOs = self::searchVideos($searchQueryDTO);

        // not working for some reason // Swoole\Server::taskWaitMulti(): taskWaitMulti method can only be used in the worker process
        //        $data = Octane::concurrently([
        //            fn() => self::searchCreators($searchQueryDTO),
        //            fn() => self::searchVideos($searchQueryDTO),
        //        ], 10000);



        // grab first 3 resultDTOs
        $first3 = array_slice($resultDTOs, 0, 3);
        $creators = [];
        foreach ($first3 as $resultDTO) {
            $creators[] = $resultDTO->creator;
        }

        // filter out creators with the same id, i.e. check $creator->id
        $creators = array_unique($creators, SORT_REGULAR);

        // create resultDTOs for the creators
        $creatorResultDTOs = [];
        foreach ($creators as $creator) {
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Creator);
            $resultDTO->creator = $creator;
            $creatorResultDTOs[] = $resultDTO;
        }

        // merge the creator resultDTOs with the video resultDTOs
        return array_merge($creatorResultDTOs, $resultDTOs);
    }

    public static function searchCreators(SearchQueryDTO $searchQueryDTO)
    {
        $yt = new self();
        $response = Http::withHeaders([
            'Scraper-Key' => $yt->scraperKey,
        ])->get("https://api.scraper.tech/search_channels.php", [
            'query' => $searchQueryDTO->query,
        ]);

        $items = $response->json()['channels'];
        $creator_ids = array_slice(array_column($response->json()['channels'], 'channelId'), 0, 2);
        $creators = self::getCreators($creator_ids);

        $results = [];
        foreach ($creators as $creator) {
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Creator);
            $resultDTO->creator = $creator;
            $results[] = $resultDTO;
        }

        return $results;
    }

    public static function searchVideos(SearchQueryDTO $searchQueryDTO)
    {
        $yt = new self();
        $response = Http::withHeaders([
            'Scraper-Key' => $yt->scraperKey,
        ])->get("https://api.scraper.tech/search_videos.php", [
            'query' => $searchQueryDTO->query,
        ]);

        $items = $response->json()['videos'];
        $video_ids = array_map(fn($item) => $item['videoId'], $items);
        $video_ids = array_slice($video_ids, 0, 5); // Limit to 3 videos
        $videos = self::getVideoOrStream($video_ids, false);

        $results = [];
        foreach ($videos as $video) {
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Video);
            $resultDTO->content = $video->content;
            $resultDTO->creator = $video->creator;
            $results[] = $resultDTO;
        }

        return $results;
    }

    public static function getVideoOrStream(array $ids, bool $returnJustContentDTO = true): array
    {
        $yt = new self();
        if (!$ids) return [];

        // Create an array of self-contained closures
        $tasks = array_map(function ($id) use ($yt) {
            return function () use ($id, $yt) {
                $response = Http::withHeaders([
                    'Scraper-Key' => $yt->scraperKey,
                ])->get("https://api.scraper.tech/video.php?video_id=$id");
                if ($response->successful()) {
                    // add video_id to the response data
                    $final = $response->json();
                    $final['data']['videoId'] = $id;
                    return $final;
                } else {
                    return null;
                }
            };
        }, $ids);

        // Execute the tasks concurrently
        $responses = Octane::concurrently($tasks, 10000);

        // Parse the responses
        $videos = [];
        foreach ($responses as $response) {
            if ($response) {
                $data = $response['data'];
                $videos[] = $data;
            }
        }


        // Extract all creator IDs from the videos
        $creatorIds = array_unique(array_column($videos, 'channelId'));

        // Fetch all creators in one batch
        $creators = self::getCreators($creatorIds);

        // Map creators by their ID for quick lookup
        $creatorsById = [];
        foreach ($creators as $creator) {
            $creatorsById[$creator->id] = $creator;
        }

        // Process videos and assign corresponding creators
        return array_map(function ($video) use ($returnJustContentDTO, $creatorsById) {
            $kind = $video['formats'][0]['durationMs'] ? Kind::Video : Kind::Stream;
            $resultDTO = new ResultDTO(Platform::YouTube, $kind);
            $contentDTO = new ContentDTO(
                Platform::YouTube,
                $kind,
                $video['videoId']
            );

            $contentDTO->creator_id = $video['channelId'];
            $contentDTO->name = $video['name'];
            $contentDTO->description = $video['description'];
            $contentDTO->duration = round($video['formats'][0]['durationMs'] / 1000);
            try {
                $contentDTO->publish_time = Carbon::parse($video['publishDate'] ?? '1970-01-01');
            } catch (\Exception $e) {
                $contentDTO->publish_time = Carbon::parse('1970-01-01');
            }
            $contentDTO->thumbnail_url = $video['thumbnails'][0]['url'] ?? "https://i.ytimg.com/vi/{$video['videoId']}/hqdefault.jpg";

            if ($kind == Kind::Stream) {
                $contentDTO->is_live = true;
            }

            $resultDTO->content = $contentDTO;

            if ($returnJustContentDTO) {
                return $contentDTO;
            }

            // Look up the creator by their ID
            $creatorDTO = $creatorsById[$video['channelId']] ?? null;

            if ($creatorDTO) {
                $resultDTO->creator = $creatorDTO;
            } else {
                // Handle the case where the creator data is not found (optional)
                $resultDTO->creator = new CreatorDTO(Platform::YouTube, $video['channelId']);
            }

            return $resultDTO;
        }, $videos);
    }
    public static function extractCreatorToDTO(array $data): CreatorDTO
    {
        $creatorDTO = new CreatorDTO(Platform::YouTube, $data['channelId']);
        $creatorDTO->name = $data['name'];
        $creatorDTO->avatar_url = $data['avatar'];
        $creatorDTO->banner_url = $data['banner'];
        $creatorDTO->description = $data['description'];

        // if region is longer than 2 characters don't set it
        if ($data['country'] && strlen($data['country']) == 2) {
            $creatorDTO->region = $data['country'];
        }


        $creatorDTO->language = $data['defaultLanguage'] ?? null;
        return $creatorDTO;
    }
}
