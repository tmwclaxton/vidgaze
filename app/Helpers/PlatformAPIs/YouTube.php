<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ApifyYoutube;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\Tools;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class YouTube implements iSearchable, iIsPlatform
{
    private const SYNC_TIMEOUT_CHANNEL = 600;

    private const SYNC_TIMEOUT_SEARCH = 480;

    private const URL_CHUNK = 20;

    public function __construct() {}

    public static function getPlatform(): Platform
    {
        return Platform::YouTube;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function syncActorInput(array $input, int $timeout = self::SYNC_TIMEOUT_SEARCH): array
    {
        try {
            return ApifyYoutube::syncDatasetItems(self::sanitizeApifyInput($input), $timeout);
        } catch (Throwable $e) {
            Log::warning('YouTube Apify sync failed: '.$e->getMessage());

            return [];
        }
    }

    public static function getCreators(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        if (count($ids) > 50) {
            throw new \Exception('Too many ids, max 50');
        }

        $byChannel = [];
        foreach (array_chunk($ids, self::URL_CHUNK) as $chunk) {
            $startUrls = array_map(
                fn (string $id) => ['url' => 'https://www.youtube.com/channel/'.rawurlencode($id)],
                $chunk
            );
            $items = self::syncActorInput([
                'startUrls' => $startUrls,
                'maxResults' => 5,
            ], self::SYNC_TIMEOUT_CHANNEL);

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $creator = self::extractCreatorFromApifyRow($item);
                if ($creator !== null) {
                    $byChannel[$creator->id] = $creator;
                }
            }
        }

        $creators = [];
        foreach ($ids as $id) {
            if (isset($byChannel[$id])) {
                $creators[] = $byChannel[$id];
            }
        }

        return $creators;
    }

    public static function search(SearchQueryDTO $searchQueryDTO): array
    {
        $resultDTOs = self::searchVideos($searchQueryDTO);

        $first3 = array_slice($resultDTOs, 0, 3);
        $creators = [];
        foreach ($first3 as $resultDTO) {
            if ($resultDTO->creator !== null) {
                $creators[] = $resultDTO->creator;
            }
        }

        $creators = array_unique($creators, SORT_REGULAR);

        $creatorResultDTOs = [];
        foreach ($creators as $creator) {
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Creator);
            $resultDTO->creator = $creator;
            $creatorResultDTOs[] = $resultDTO;
        }

        return array_merge($creatorResultDTOs, $resultDTOs);
    }

    public static function searchCreators(SearchQueryDTO $searchQueryDTO): array
    {
        $items = self::syncActorInput([
            'searchQueries' => [$searchQueryDTO->query],
            'maxResults' => min(50, max(5, $searchQueryDTO->max_results)),
        ], self::SYNC_TIMEOUT_SEARCH);

        $seen = [];
        $results = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $creator = self::extractCreatorFromApifyRow($item);
            if ($creator === null || isset($seen[$creator->id])) {
                continue;
            }
            $seen[$creator->id] = true;
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Creator);
            $resultDTO->creator = $creator;
            $results[] = $resultDTO;
            if (count($results) >= $searchQueryDTO->max_results) {
                break;
            }
        }

        return Tools::validateDTOs($results);
    }

    public static function searchVideos(SearchQueryDTO $searchQueryDTO): array
    {
        $items = self::syncActorInput([
            'searchQueries' => [$searchQueryDTO->query],
            'maxResults' => min(50, max(5, $searchQueryDTO->max_results)),
        ], self::SYNC_TIMEOUT_SEARCH);

        $out = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $dto = self::apifyRowToVideoResult($item);
            if ($dto !== null) {
                $out[] = $dto;
            }
        }

        $cap = min(max(1, $searchQueryDTO->max_results), count($out));

        return Tools::validateDTOs(array_slice($out, 0, $cap));
    }

    public static function getVideoOrStream(array $ids, bool $returnJustContentDTO = true): array
    {
        if ($ids === []) {
            return [];
        }

        $videosMeta = [];
        foreach (array_chunk($ids, self::URL_CHUNK) as $chunk) {
            $startUrls = array_map(
                fn (string $id) => ['url' => 'https://www.youtube.com/watch?v='.rawurlencode($id)],
                $chunk
            );

            $items = self::syncActorInput([
                'startUrls' => $startUrls,
                'maxResults' => count($chunk) + 5,
            ], self::SYNC_TIMEOUT_CHANNEL);

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $vid = self::resolveApifyVideoId($item);
                if ($vid === null) {
                    continue;
                }
                $videosMeta[$vid] = $item;
            }
        }

        $creatorIds = [];
        foreach ($videosMeta as $item) {
            $cid = self::resolveApifyChannelId($item);
            if ($cid !== null) {
                $creatorIds[] = $cid;
            }
        }
        $creatorIds = array_values(array_unique($creatorIds));

        $creators = self::getCreators($creatorIds);
        $creatorsById = [];
        foreach ($creators as $creator) {
            $creatorsById[$creator->id] = $creator;
        }

        $built = [];
        foreach ($ids as $id) {
            $item = $videosMeta[$id] ?? null;
            if (! is_array($item)) {
                continue;
            }
            $built[] = self::buildResultFromApifyItem($item, $returnJustContentDTO, $creatorsById);
        }

        return array_values(array_filter($built));
    }

    public static function extractCreatorToDTO(array $data): CreatorDTO
    {
        $channelId = $data['channelId'] ?? $data['id'] ?? null;
        if ($channelId === null && ! empty($data['channelUrl'])) {
            $channelId = self::parseChannelIdFromUrl((string) $data['channelUrl']);
        }
        if ($channelId === null) {
            $channelId = 'unknown';
        }

        $creatorDTO = new CreatorDTO(Platform::YouTube, $channelId);
        $creatorDTO->name = $data['name'] ?? $data['channelName'] ?? $data['title'] ?? 'Unknown';
        $creatorDTO->avatar_url = $data['avatar'] ?? $data['channelThumbnail'] ?? $data['thumbnailUrl'] ?? '';
        $creatorDTO->banner_url = $data['banner'] ?? $data['channelBanner'] ?? null;
        $creatorDTO->description = $data['description'] ?? $data['channelDescription'] ?? '';
        $country = $data['country'] ?? null;
        if ($country && strlen((string) $country) === 2) {
            $creatorDTO->region = (string) $country;
        }
        $creatorDTO->language = $data['defaultLanguage'] ?? $data['channelLanguage'] ?? null;

        return $creatorDTO;
    }

    public static function getCreatorVideosBeforeDate(string $id, ?Carbon $date = null, $maxResults = 50, bool $includeStreams = true, bool $onlyStreams = false): array
    {
        if ($maxResults > 50) {
            throw new \Exception('Max results cannot be greater than 50');
        }

        $items = self::syncActorInput([
            'startUrls' => [['url' => 'https://www.youtube.com/channel/'.rawurlencode($id).'/videos']],
            'maxResults' => $maxResults,
            'sortVideosBy' => 'NEWEST',
        ], self::SYNC_TIMEOUT_CHANNEL);

        $contentDtos = [];
        $lastPublish = null;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $videoId = self::resolveApifyVideoId($item);
            if ($videoId === null) {
                continue;
            }

            $durationMs = self::parseDurationToMs($item);
            $isLive = self::rowIndicatesLive($item);
            if (! $includeStreams && ($isLive || $durationMs === 0)) {
                continue;
            }
            if ($onlyStreams && ! $isLive) {
                continue;
            }

            $published = self::parseApifyDate($item);
            if ($date instanceof Carbon && $published && $published->greaterThan($date)) {
                continue;
            }

            $contentDTO = new ContentDTO(Platform::YouTube, Kind::Video, $videoId);
            $contentDTO->creator_id = $id;
            $contentDTO->name = self::apifyString($item, ['title', 'name']) ?? 'Untitled';
            $contentDTO->description = self::apifyString($item, ['description', 'text']) ?? '';
            $contentDTO->duration = (int) round($durationMs / 1000);
            $contentDTO->publish_time = $published ?? Carbon::now();
            $contentDTO->thumbnail_url = self::apifyThumbnail($item) ?? "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg";

            if ($isLive || $durationMs === 0) {
                $contentDTO->kind = Kind::Stream;
                $contentDTO->is_live = true;
            }

            $contentDtos[] = $contentDTO;
            $lastPublish = $published ?? $lastPublish;

            if (count($contentDtos) >= $maxResults) {
                break;
            }
        }

        return [
            'next' => $lastPublish,
            'hasNext' => count($contentDtos) >= $maxResults,
            'results' => $contentDtos,
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

    private static function extractCreatorFromApifyRow(array $item): ?CreatorDTO
    {
        $channelId = self::resolveApifyChannelId($item);
        if ($channelId === null) {
            return null;
        }

        $data = [
            'channelId' => $channelId,
            'name' => self::apifyString($item, ['channelName', 'channelTitle', 'uploaderName', 'author']),
            'avatar' => self::apifyChannelAvatar($item),
            'banner' => $item['channelBannerUrl'] ?? $item['channelBanner'] ?? null,
            'description' => self::apifyString($item, ['channelDescription', 'channelAbout']),
            'country' => $item['channelCountry'] ?? null,
            'defaultLanguage' => $item['channelLanguage'] ?? null,
        ];

        if (($data['name'] ?? null) === null || $data['name'] === '') {
            $data['name'] = self::apifyString($item, ['title']) ?? 'Unknown';
        }

        return self::extractCreatorToDTO($data);
    }

    private static function apifyRowToVideoResult(array $item): ?ResultDTO
    {
        return self::buildResultFromApifyItem($item, false, []);
    }

    /**
     * @param  array<string, CreatorDTO>  $creatorsById
     */
    private static function buildResultFromApifyItem(array $item, bool $returnJustContentDTO, array $creatorsById): ResultDTO|ContentDTO|null
    {
        $videoId = self::resolveApifyVideoId($item);
        if ($videoId === null) {
            return null;
        }

        $channelId = self::resolveApifyChannelId($item) ?? '';

        $durationMs = self::parseDurationToMs($item);
        $isLive = self::rowIndicatesLive($item);
        $kind = ($isLive || $durationMs === 0) ? Kind::Stream : Kind::Video;

        $contentDTO = new ContentDTO(Platform::YouTube, $kind, $videoId);
        $contentDTO->creator_id = $channelId;
        $contentDTO->name = self::apifyString($item, ['title', 'name']) ?? 'Untitled';
        $contentDTO->description = self::apifyString($item, ['description', 'text']) ?? '';
        $contentDTO->duration = (int) round($durationMs / 1000);
        $contentDTO->publish_time = self::parseApifyDate($item) ?? Carbon::now();
        $contentDTO->thumbnail_url = self::apifyThumbnail($item) ?? "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg";
        $contentDTO->views = self::apifyInt($item['viewCount'] ?? $item['views'] ?? null) ?? 0;

        if ($kind === Kind::Stream) {
            $contentDTO->is_live = true;
        }

        if ($returnJustContentDTO) {
            return $contentDTO;
        }

        $resultDTO = new ResultDTO(Platform::YouTube, $kind);
        $resultDTO->content = $contentDTO;
        if ($channelId !== '' && isset($creatorsById[$channelId])) {
            $resultDTO->creator = $creatorsById[$channelId];
        } else {
            $c = self::extractCreatorFromApifyRow($item);
            $resultDTO->creator = $c ?? new CreatorDTO(Platform::YouTube, $channelId !== '' ? $channelId : 'unknown');
        }

        return $resultDTO;
    }

    private static function resolveApifyVideoId(array $item): ?string
    {
        $candidates = [
            $item['id'] ?? null,
            $item['videoId'] ?? null,
        ];
        foreach ($candidates as $c) {
            if (is_string($c) && preg_match('/^[a-zA-Z0-9_-]{6,}$/', $c)) {
                return $c;
            }
        }
        foreach (['url', 'videoUrl', 'link'] as $key) {
            if (! empty($item[$key]) && is_string($item[$key])) {
                if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $item[$key], $m)) {
                    return $m[1];
                }
            }
        }

        return null;
    }

    private static function resolveApifyChannelId(array $item): ?string
    {
        if (! empty($item['channelId']) && is_string($item['channelId'])) {
            return $item['channelId'];
        }
        foreach (['channelUrl', 'channelURL', 'uploaderUrl', 'authorUrl'] as $k) {
            if (! empty($item[$k])) {
                $parsed = self::parseChannelIdFromUrl((string) $item[$k]);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        }

        return null;
    }

    private static function parseChannelIdFromUrl(string $url): ?string
    {
        if (preg_match('#youtube\.com/channel/([^/?#]+)#', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    private static function parseDurationToMs(array $item): int
    {
        $raw = $item['durationMs'] ?? $item['duration'] ?? null;
        if ($raw === null) {
            return 0;
        }
        if (is_int($raw) || (is_string($raw) && ctype_digit($raw))) {
            $n = (int) $raw;
            if ($n > 360_000) {
                return $n;
            }

            return $n * 1000;
        }
        if (is_string($raw)) {
            if (preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?$/i', $raw, $m)) {
                $h = (int) ($m[1] ?? 0);
                $min = (int) ($m[2] ?? 0);
                $s = (float) ($m[3] ?? 0);

                return (int) (($h * 3600 + $min * 60 + $s) * 1000);
            }
            if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $raw, $m)) {
                if (isset($m[3])) {
                    return ((int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3]) * 1000;
                }

                return ((int) $m[1] * 60 + (int) $m[2]) * 1000;
            }
        }

        return 0;
    }

    private static function rowIndicatesLive(array $item): bool
    {
        if (! empty($item['isUpcoming'])) {
            return false;
        }
        foreach (['isLive', 'live'] as $k) {
            if (! empty($item[$k])) {
                return true;
            }
        }
        $title = strtolower((string) ($item['title'] ?? ''));
        if (str_contains($title, 'live stream') || str_contains($title, '🔴')) {
            return true;
        }

        return false;
    }

    private static function parseApifyDate(array $item): ?Carbon
    {
        $raw = $item['uploadDate'] ?? $item['date'] ?? $item['publishedAt'] ?? $item['publishDate'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }
        if ($raw instanceof Carbon) {
            return $raw;
        }
        try {
            return Carbon::parse((string) $raw);
        } catch (Throwable) {
            return null;
        }
    }

    private static function apifyThumbnail(array $item): ?string
    {
        $keys = ['thumbnailUrl', 'thumbnail', 'coverImage', 'bestThumbnail', 'image'];
        foreach ($keys as $k) {
            if (! empty($item[$k])) {
                if (is_string($item[$k])) {
                    return $item[$k];
                }
                if (is_array($item[$k]) && isset($item[$k]['url'])) {
                    return (string) $item[$k]['url'];
                }
            }
        }
        if (! empty($item['thumbnails'][0]['url'])) {
            return (string) $item['thumbnails'][0]['url'];
        }

        return null;
    }

    private static function apifyChannelAvatar(array $item): string
    {
        foreach (['channelThumbnail', 'channelAvatar', 'authorThumbnail'] as $k) {
            if (! empty($item[$k])) {
                if (is_string($item[$k])) {
                    return $item[$k];
                }
            }
        }
        if (! empty($item['authorThumbnails'][0]['url'])) {
            return (string) $item['authorThumbnails'][0]['url'];
        }

        return '';
    }

    /**
     * @param  list<string>  $keys
     */
    private static function apifyString(array $item, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (isset($item[$k]) && is_string($item[$k]) && $item[$k] !== '') {
                return $item[$k];
            }
        }

        return null;
    }

    private static function apifyInt(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_int($v)) {
            return $v;
        }
        if (is_string($v) && preg_match('/(\d+)/', str_replace(',', '', $v), $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private static function sanitizeApifyInput(array $input): array
    {
        if (isset($input['searchQueries']) && is_array($input['searchQueries'])) {
            $input['searchQueries'] = array_values(array_filter(
                array_map(static fn ($q) => is_string($q) ? trim($q) : '', $input['searchQueries'])
            ));
        }

        return $input;
    }
}
