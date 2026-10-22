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
use Illuminate\Support\Facades\Log;
use App\Helpers\Scraping\Crawl4AI;
use App\Helpers\Scraping\YouTubeParser;
use Laravel\Octane\Facades\Octane;

class YouTube implements iSearchable, iIsPlatform
{
    protected string $scraperKey;

    public function __construct()
    {
        $this->scraperKey = (string) config('services.scraper.key'); // Retrieve the Scraper-Key from the environment
    }

    public static function getPlatform(): Platform
    {
        return Platform::YouTube;
    }

    public static function scraperTechGetCreators(array $ids): array
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

        return Tools::validateDTOs($results);
    }

    public static function scraperTechSearchVideos(SearchQueryDTO $searchQueryDTO)
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

        return Tools::validateDTOs($results);
    }

    public static function scraperTechGetVideoOrStream(array $ids, bool $returnJustContentDTO = true): array
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
                $contentDTO->publish_time = Carbon::parse($video['publishDate']);
            } catch (\Exception $e) {
                // set 1st January 2000 as default publish time
                $contentDTO->publish_time = Carbon::now();
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


    public static function scraperTechGetCreatorVideosBeforeDate(string $id, ?Carbon $date = null, $maxResults = 50, bool $includeStreams = true, bool $onlyStreams = false): array
    {
        if ($maxResults > 50) throw new \Exception('Max results cannot be greater than 50');
        $yt = new self();

        $response = Http::withHeaders([
            'Scraper-Key' => $yt->scraperKey,
        ])->get("https://api.scraper.tech/feed.php?channel_id=$id");

        $items = $response->json()['videos'];

        $results = array_map(function($value) use ($id) {
            $contentDTO = new ContentDTO(
                Platform::YouTube,
                Kind::Video,
                $value['videoId']
            );

            $contentDTO->creator_id = $id;
            $contentDTO->name = $value['title'];
            $contentDTO->description = "Description not available";
            $contentDTO->duration = Tools::convertColonSeparatedTimeToSeconds($value['length']);
            // grab time string at end of  "label" => "Giving you guys a chance.... by PewDiePie 2,298,372 views 2 months ago 21 minutes"
            // i.e. everything after "views"
            $timeString = substr($value['label'], strpos($value['label'], 'views') + 5);
            $contentDTO->publish_time = Carbon::parse($timeString);
            $contentDTO->thumbnail_url = "https://i.ytimg.com/vi/{$value['videoId']}/hqdefault.jpg";

            return $contentDTO;
        }, $items);

        $lastItem = end($items);
        $lastDateString = substr($lastItem['label'], strpos($lastItem['label'], 'views') + 5);
        $lastDate = Carbon::parse($lastDateString);
        return [
            'next' => $lastDate,
            'hasNext' => count($items) >= $maxResults,
            'results' => $results,
        ];
    }

    /*
     * Data sources, in order:
     *  1. self hosted crawl4ai for search and watch pages (free), plus the
     *     public channel RSS feed (free, no key).
     *  2. scraper.tech, only when SCRAPER_KEY is set and the free path
     *     returned nothing.
     */

    public static function hasScraperTech(): bool
    {
        return !empty(config('services.scraper.key'));
    }

    private static function feed(string $channelId): ?array
    {
        try {
            $response = Http::timeout(20)->get('https://www.youtube.com/feeds/videos.xml', ['channel_id' => $channelId]);
            return $response->successful() ? YouTubeParser::parseFeed($response->body()) : null;
        } catch (\Throwable $e) {
            Log::warning("YouTube feed failed for $channelId: " . $e->getMessage());
            return null;
        }
    }

    private static function creatorFromParsed(array $v): CreatorDTO
    {
        $creatorDTO = new CreatorDTO(Platform::YouTube, $v['channelId']);
        $creatorDTO->name = $v['channelName'];
        $creatorDTO->avatar_url = $v['channelAvatar'] ?? null;
        $creatorDTO->banner_url = null;
        $creatorDTO->description = '';
        $creatorDTO->language = null;
        return $creatorDTO;
    }

    private static function contentFromParsed(array $v): ContentDTO
    {
        $kind = !empty($v['is_live']) ? Kind::Stream : Kind::Video;
        $contentDTO = new ContentDTO(Platform::YouTube, $kind, $v['videoId']);
        $contentDTO->creator_id = $v['channelId'];
        $contentDTO->name = $v['title'];
        $contentDTO->description = $v['description'] ?? '';
        $contentDTO->duration = (string) ($v['duration'] ?? 0);
        $contentDTO->publish_time = $v['publish_time'];
        $contentDTO->thumbnail_url = $v['thumbnail'] ?? "https://i.ytimg.com/vi/{$v['videoId']}/hqdefault.jpg";
        if ($kind === Kind::Stream) {
            $contentDTO->is_live = true;
        }
        return $contentDTO;
    }

    /**
     * Channel details. Channel pages are behind YouTube's EU consent wall for
     * a headless browser on this server, so the name comes from the RSS feed
     * and the avatar from the newest video's watch page.
     */
    public static function getCreators(array $ids): array
    {
        if (!$ids) return [];
        if (count($ids) > 50) {
            throw new \Exception('Too many ids, max 50');
        }

        $creators = [];
        if (Crawl4AI::enabled()) {
            $latest = [];
            foreach (array_values(array_unique($ids)) as $id) {
                $feed = self::feed($id);
                if (!$feed || $feed['name'] === '') continue;
                $creatorDTO = new CreatorDTO(Platform::YouTube, $id);
                $creatorDTO->name = $feed['name'];
                $creatorDTO->avatar_url = null;
                $creatorDTO->banner_url = null;
                $creatorDTO->description = '';
                $creatorDTO->language = null;
                $creators[$id] = $creatorDTO;
                if (!empty($feed['videos'][0]['videoId'])) {
                    $latest[$id] = 'https://www.youtube.com/watch?v=' . $feed['videos'][0]['videoId'];
                }
            }
            if ($latest) {
                $pages = (new Crawl4AI())->htmlMany(array_values($latest));
                foreach ($latest as $id => $url) {
                    $watch = !empty($pages[$url]) ? YouTubeParser::parseWatch($pages[$url]) : null;
                    if ($watch && $watch['channelId'] === $id) {
                        $creators[$id]->avatar_url = $watch['channelAvatar'];
                    }
                }
            }
        }

        $missing = array_values(array_diff($ids, array_keys($creators)));
        if ($missing && self::hasScraperTech()) {
            foreach (self::scraperTechGetCreators($missing) as $c) {
                $creators[$c->id] = $c;
            }
        }

        return array_values($creators);
    }

    public static function searchVideos(SearchQueryDTO $searchQueryDTO)
    {
        $results = [];
        if (Crawl4AI::enabled()) {
            $html = (new Crawl4AI())->html('https://www.youtube.com/results?search_query=' . urlencode($searchQueryDTO->query));
            foreach ($html ? YouTubeParser::parseSearch($html, 5) : [] as $v) {
                $content = self::contentFromParsed($v);
                $resultDTO = new ResultDTO(Platform::YouTube, $content->kind);
                $resultDTO->content = $content;
                $resultDTO->creator = self::creatorFromParsed($v);
                $results[] = $resultDTO;
            }
        }

        if (!$results && self::hasScraperTech()) {
            return self::scraperTechSearchVideos($searchQueryDTO);
        }

        return Tools::validateDTOs($results);
    }

    public static function getVideoOrStream(array $ids, bool $returnJustContentDTO = true): array
    {
        if (!$ids) return [];
        $out = [];
        $missing = $ids;

        if (Crawl4AI::enabled()) {
            $urls = [];
            foreach ($ids as $id) $urls[$id] = "https://www.youtube.com/watch?v=$id";
            $pages = (new Crawl4AI())->htmlMany(array_values($urls));
            $missing = [];
            foreach ($urls as $id => $url) {
                $v = !empty($pages[$url]) ? YouTubeParser::parseWatch($pages[$url]) : null;
                if (!$v) { $missing[] = $id; continue; }
                $content = self::contentFromParsed($v);
                if ($returnJustContentDTO) {
                    $out[] = $content;
                } else {
                    $resultDTO = new ResultDTO(Platform::YouTube, $content->kind);
                    $resultDTO->content = $content;
                    $resultDTO->creator = self::creatorFromParsed($v);
                    $out[] = $resultDTO;
                }
            }
        }

        if ($missing && self::hasScraperTech()) {
            $out = array_merge($out, self::scraperTechGetVideoOrStream($missing, $returnJustContentDTO));
        }

        return $out;
    }

    /**
     * Latest uploads from the public RSS feed (up to 15, no key needed).
     * Durations are not in the feed, so they are left at 0.
     */
    public static function getCreatorVideosBeforeDate(string $id, ?Carbon $date = null, $maxResults = 50, bool $includeStreams = true, bool $onlyStreams = false): array
    {
        if ($maxResults > 50) throw new \Exception('Max results cannot be greater than 50');

        $feed = self::feed($id);
        if ((!$feed || !$feed['videos']) && self::hasScraperTech()) {
            return self::scraperTechGetCreatorVideosBeforeDate($id, $date, $maxResults, $includeStreams, $onlyStreams);
        }

        $videos = $feed['videos'] ?? [];
        if ($date) {
            $videos = array_values(array_filter($videos, fn ($v) => $v['publish_time']->lt($date)));
        }
        $videos = array_slice($videos, 0, $maxResults);

        $results = array_map(function ($v) use ($id) {
            $v['channelId'] = $id;
            $v['duration'] = 0;
            return self::contentFromParsed($v);
        }, $videos);

        $last = end($videos);
        return [
            'next' => $last ? $last['publish_time'] : null,
            // the feed only exposes the newest 15 uploads, so there is no next page
            'hasNext' => false,
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
