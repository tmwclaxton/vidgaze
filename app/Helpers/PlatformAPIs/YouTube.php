<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Audience;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanUpload;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iHaveVideos;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanLogin;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\Tools;
use App\Helpers\UploadDTO;
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


        $creators = [];
        foreach ($ids as $id) {
            $response = Http::withHeaders([
                'Scraper-Key' => $yt->scraperKey,
            ])->get("https://api.scraper.tech/channel.php?channel_id=$id");

            if ($response->successful()) {
                $data = $response->json()['data'];
                $creators[] = self::extractCreatorToDTO($data);
            }
        }

        return $creators;
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
        $creator_ids = array_map(fn($item) => $item['channelId'], $items);
        // filter to 2 creators
        $creator_ids = array_slice($creator_ids, 0, 2);
        $data = self::getCreators($creator_ids);

        $results = [];
        foreach ($data as $creator) {
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Creator);
            $resultDTO->creator = $creator;
            $results[] = $resultDTO;
        }

        return $results;
    }


    // search for videos and streams (streams can be differentiated by not having a duration)
//    curl -X GET "https://api.scraper.tech/search_videos.php?query=apple" \
//     -H "scraper-key: 69516617770b3a34968e06b33cd2d234" \
//     -m 30 \
//     -o response.json

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

        // filter to 3 videos
        $video_ids = array_slice($video_ids, 0, 3);

        $data = self::getVideoOrStream($video_ids);

        $results = [];
        foreach ($data as $video) {
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Video);
            $resultDTO->content = $video;
            $results[] = $resultDTO;
        }

        return $results;
    }

    // search we need to get both creators and videos using the searchVideos and searchCreators methods
    public static function search(SearchQueryDTO $searchQueryDTO)
    {



    }


//    public static function search(SearchQueryDTO $searchQueryDTO){
//        $yt = new self();
//        $response = $yt->client->search->listSearch(['snippet'], [
//            'q' => $searchQueryDTO->query,
//            'maxResults' => ($searchQueryDTO->max_results <= 50) ? $searchQueryDTO->max_results : 50,
//        ]);
//
//        $items = $response->getItems();
//        $separate_items = [
//            'video_and_stream_ids' => [],
//            'all_creator_ids' => [],
//        ];
//        foreach ($items as $item) {
//            match ($item['id']['kind']) {
//                'youtube#video' => $separate_items['video_and_stream_ids'][] = $item['id']['videoId'],
//                'youtube#channel' => $separate_items['creator_ids'][] = ($item['snippet']['channelId']) ?: $item['id']['channelId'],
//                'youtube#playlist' => $separate_items['playlist_ids'][] = $item['id']['playlistId'],
//            };
//            $separate_items['all_creator_ids'][] = ($item['snippet']['channelId']) ?: $item['id']['channelId'];
//        }
//
//        $data =  Octane::concurrently([
//            fn() => self::getCreators($separate_items['all_creator_ids']),
//            fn() => self::getVideoOrStream($separate_items['video_and_stream_ids'], false)
//        ],5000);
//
//        // foreach video and stream, add corresponding creator to DTO
//        foreach ($data[1] as $item) {
//            $item->creator = $data[0][array_search($item->content->creator_id, array_column($data[0], 'id'))];
//        }
//
//        $creatorDTOs = array_filter($data[0], function ($creator) use ($separate_items) {
//            return in_array($creator->id, $separate_items['creator_ids']);
//        });
//
//        $results = [];
//        foreach ($creatorDTOs as $creator) {
//            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Creator);
//            $resultDTO->creator = $creator;
//            $results[] = $resultDTO;
//        }
//        // only return creators in the creator_ids array (and other results)
//        return array_merge($results, $data[1]);
//    }

    public static function getVideoOrStream(array $ids, bool $returnJustContentDTO = true): array
    {
        $yt = new self();
        if (!$ids) return [];
        $videos = [];

        foreach ($ids as $id) {
            $response = Http::withHeaders([
                'Scraper-Key' => $yt->scraperKey,
            ])->get("https://api.scraper.tech/video.php?video_id=$id");

            if ($response->successful()) {
                $data = $response->json()['data'];
                $data['videoId'] = $id;
                $videos[] = $data;
            }
        }

        return array_map(function ($video) use ($returnJustContentDTO) {
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
//            $contentDTO->duration = Tools::convertYouTubeDurationToSeconds($video['formats'][0]['durationMs']);

            // convert ms to seconds
            $contentDTO->duration = round($video['formats'][0]['durationMs'] / 1000);

            try {
                $contentDTO->publish_time = Carbon::parse($video['publishDate']);
            } catch (\Exception $e) {
                // put epoch time if date is not available so can be fixed later
                $contentDTO->publish_time = Carbon::createFromTimestamp(0);
            }

            if (isset($video['thumbnails'][0]['url'])) {
                $contentDTO->thumbnail_url = $video['thumbnails'][0]['url'];
            } else {
                $contentDTO->thumbnail_url = "https://i.ytimg.com/vi/{$video['videoId']}/hqdefault.jpg";
            }

            if($kind == Kind::Stream){
                $contentDTO->is_live = true;
            }

            $resultDTO->content = $contentDTO;

            if ($returnJustContentDTO) return $contentDTO;

            $resultDTO->kind = $kind;
            $resultDTO->creator = new CreatorDTO(Platform::YouTube, $video['channelId']);
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
        $creatorDTO->region = $data['country'] ?? null;
        $creatorDTO->language = $data['defaultLanguage'] ?? null;
        return $creatorDTO;
    }

    public static function getCreatorVideosBeforeDate(string $id, Carbon $date = null, $maxResults = 50, bool $includeStreams = true, bool $onlyStreams = false): array
    {
        if ($maxResults > 50) throw new \Exception('Max results cannot be greater than 50');
        $yt = new self();

        $response = Http::withHeaders([
            'Scraper-Key' => $yt->scraperKey,
        ])->get("https://api.scraper.tech/feed.php?channel_id=$id");

        $items = $response->json()['videos'];
        $results = self::getVideoOrStream(array_map(fn($item) => $item['videoId'], $items));

        return [
            'next' => end($items) ? Carbon::make(end($items)['publishDate']) : null,
            'hasNext' => count($items) >= $maxResults,
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
