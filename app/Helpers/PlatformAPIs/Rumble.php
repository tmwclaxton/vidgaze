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
use App\Helpers\Scraping\Crawl4AI;
use App\Helpers\Scraping\RumbleParser;
use App\Helpers\Tools;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Rumble implements iSearchable, iIsPlatform
{


    public static function getPlatform(): Platform
    {
        return Platform::Rumble;
    }

    /** Build a ResultDTO for a creator from parsed channel data. */
    private static function creatorResult(string $slug, array $c): ResultDTO
    {
        $resultDTO = new ResultDTO(Platform::Rumble, Kind::Creator);
        $creatorDTO = new CreatorDTO(Platform::Rumble, $slug);
        $creatorDTO->kind = Kind::Creator;
        $creatorDTO->name = $c['name'];
        $creatorDTO->description = $c['description'] ?? '';
        $creatorDTO->avatar_url = $c['avatar'] ?? null;
        $creatorDTO->banner_url = $c['banner'] ?? null;
        $resultDTO->creator = $creatorDTO;
        return $resultDTO;
    }

    /** Build a video ResultDTO from a normalised video row. */
    private static function videoResult(array $v, ?CreatorDTO $creator = null): ?ResultDTO
    {
        $slug = $creator?->id ?? ($v['channel_slug'] ?? null);
        if (empty($v['id']) || empty($v['title']) || !$slug) {
            return null;
        }
        if (!$creator) {
            $creator = new CreatorDTO(Platform::Rumble, $slug);
            $creator->kind = Kind::Creator;
            $creator->name = $v['channel_name'] ?? $slug;
            $creator->description = '';
            $creator->avatar_url = $v['channel_avatar'] ?? null;
        }
        $resultDTO = new ResultDTO(Platform::Rumble, Kind::Video);
        $contentDTO = new ContentDTO(Platform::Rumble, Kind::Video, $v['id']);
        $contentDTO->kind = Kind::Video;
        $contentDTO->name = $v['title'];
        $contentDTO->duration = (string) ($v['duration'] ?? 0);
        $contentDTO->publish_time = !empty($v['upload_date']) ? Carbon::make($v['upload_date']) : Carbon::now();
        $contentDTO->thumbnail_url = $v['thumb'] ?? '';
        $contentDTO->tags = $v['tags'] ?? [];
        $contentDTO->description = $v['description'] ?? "This video was uploaded by {$creator->name} on Rumble.";
        $contentDTO->creator_id = $slug;
        $resultDTO->content = $contentDTO;
        $resultDTO->creator = $creator;
        return $resultDTO;
    }

    /**
     * Map a memo23~rumble-scraper row (fetchDetails=false, flatten=false)
     * into the normalised video shape.
     */
    public static function mapApifyVideoRow(array $row): ?array
    {
        if (($row['type'] ?? 'video') !== 'video') return null;
        $embed = $row['embedId'] ?? RumbleParser::embedId($row['id'] ?? '');
        return [
            'id' => $embed,
            'title' => $row['title'] ?? null,
            'duration' => (int) ($row['durationSeconds'] ?? 0),
            'upload_date' => $row['uploadedDate'] ?? null,
            'thumb' => $row['thumbnail'] ?? null,
            'channel_name' => $row['channelName'] ?? null,
            'channel_slug' => RumbleParser::slugFromUrl($row['channelUrl'] ?? null),
        ];
    }

    /** Cheapest input for the Rumble listing actor (see config/services.php). */
    private static function apifyListingInput(array $extra): array
    {
        return array_merge([
            'fetchDetails' => false,       // listing data is all vidgaze stores
            'extractTranscript' => false,
            'includeChannelInfo' => false,
            'flatten' => false,
            'maxPagesPerList' => 1,
            'detailConcurrency' => 1,
            'proxy' => Apify::proxyConfig(),
        ], $extra);
    }

    public static function getCreators(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(Arr::flatten($ids))));
        if (!$ids) return [];

        $results = [];
        $missing = $ids;

        if (Crawl4AI::enabled()) {
            $urls = [];
            foreach ($ids as $id) $urls[$id] = "https://rumble.com/c/$id";
            $pages = (new Crawl4AI())->htmlMany(array_values($urls));
            $missing = [];
            foreach ($urls as $id => $url) {
                $parsed = !empty($pages[$url]) ? RumbleParser::parseChannel($pages[$url]) : null;
                if ($parsed) {
                    $results[] = self::creatorResult($id, $parsed);
                } else {
                    $missing[] = $id;
                }
            }
        }

        if ($missing && Apify::enabled()) {
            $max = (int) config('services.apify.rumble.creators_max', 10);
            $batch = array_slice($missing, 0, $max);
            $items = (new Apify(config('services.apify.rumble.creator_actor')))->run([
                'startUrls' => array_map(fn ($id) => "https://rumble.com/c/$id", $batch),
                'scrapeChannelVideos' => false,
                'scrapeChannelPlaylists' => false,
                'maxVideoToScrapeFromChannel' => 1,
            ], count($batch), 'rumble getCreators: crawl4ai failed for ' . count($missing) . ' channel(s)');
            foreach ($items ?? [] as $value) {
                if (empty($value['slug']) || empty($value['name'])) continue;
                $results[] = self::creatorResult($value['slug'], [
                    'name' => $value['name'],
                    'description' => $value['description'] ?? '',
                    'avatar' => $value['thumb'] ?? null,
                    'banner' => $value['backsplash'] ?? null,
                ]);
            }
        }

        return Tools::validateDTOs($results);
    }

    public static function search(SearchQueryDTO $searchQueryDTO): array
    {
        $limit = (int) config('services.apify.rumble.search_max', 20);
        $parsed = ['videos' => [], 'channels' => []];

        if (Crawl4AI::enabled()) {
            $attempts = 1 + max(0, (int) config('services.crawl4ai.retries', 2));
            for ($i = 0; $i < $attempts && !$parsed['videos']; $i++) {
                $html = (new Crawl4AI())->html('https://rumble.com/search/all?q=' . urlencode($searchQueryDTO->query));
                if ($html) {
                    $parsed = RumbleParser::parseSearch($html, $limit, 3);
                }
            }
        }

        if (!$parsed['videos'] && Apify::enabled()) {
            $items = (new Apify(config('services.apify.rumble.actor')))->run(self::apifyListingInput([
                'searchQueries' => [$searchQueryDTO->query],
                'searchType' => 'video',
                'maxItems' => $limit,
            ]), $limit, 'rumble search: crawl4ai returned no videos');
            $parsed['videos'] = array_values(array_filter(array_map([self::class, 'mapApifyVideoRow'], $items ?? [])));
        }

        $results = [];
        foreach ($parsed['channels'] as $c) {
            $results[] = self::creatorResult($c['slug'], ['name' => $c['name'], 'description' => '']);
        }
        foreach ($parsed['videos'] as $v) {
            if ($r = self::videoResult($v)) $results[] = $r;
        }

        return Tools::validateDTOs(array_slice($results, 0, $limit + 3));
    }

    public static function getCreatorVideos(string $id, ?int $page = null, $maxResults = 100): array
    {
        if ($maxResults > 100) {
            throw new \Exception('Max results cannot be greater than 100');
        }
        $page = $page ?? 1;
        $empty = ['next' => null, 'hasNext' => false, 'results' => [], 'channel' => null];

        $channel = null;
        $videos = [];

        if (Crawl4AI::enabled()) {
            // Rumble A/B tests a channel layout that only renders the grid with
            // client side JS. Retry, since the next response is usually the
            // server rendered layout.
            $attempts = 1 + max(0, (int) config('services.crawl4ai.retries', 2));
            for ($i = 0; $i < $attempts && !$videos; $i++) {
                $url = "https://rumble.com/c/$id/videos" . ($page > 1 ? "?page=$page" : '');
                $html = (new Crawl4AI())->html($url);
                if ($html) {
                    $channel = RumbleParser::parseChannel($html) ?? $channel;
                    $videos = RumbleParser::parseChannelVideos($html, $maxResults);
                }
            }
        }

        if (!$videos && Apify::enabled()) {
            $limit = min($maxResults, (int) config('services.apify.rumble.channel_videos_max', 30));
            $items = (new Apify(config('services.apify.rumble.actor')))->run(self::apifyListingInput([
                'startUrls' => [['url' => "https://rumble.com/c/$id"]],
                'maxItems' => $limit,
            ]), $limit, "rumble getCreatorVideos($id): crawl4ai returned no videos");
            $videos = array_values(array_filter(array_map([self::class, 'mapApifyVideoRow'], $items ?? [])));
            if ($videos) {
                $channel = ['name' => $videos[0]['channel_name'] ?? $id, 'description' => ''];
            }
        }

        if (!$videos) {
            return $empty;
        }

        $creatorDTO = self::creatorResult($id, $channel ?? ['name' => $videos[0]['channel_name'] ?? $id])->creator;
        $results = array_values(array_filter(array_map(fn ($v) => self::videoResult($v, $creatorDTO), $videos)));

        // A full Rumble channel page holds about 25 videos; fewer means last page.
        $hasNext = count($videos) >= 20;

        return [
            'next' => $hasNext ? $page + 1 : null,
            'hasNext' => $hasNext,
            'results' => Tools::validateDTOs($results),
            'channel' => $channel,
        ];
    }

}
