<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ApifyPodcast;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use App\Models\PodcastModels\Podcast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;

class Podcasts implements iIsPlatform, iSearchable
{
    public static function getPlatform(): Platform
    {
        return Platform::Podcasts;
    }

    /**
     * @return list<ResultDTO>
     */
    public static function search(SearchQueryDTO $searchQuery): array
    {
        $items = self::searchApify($searchQuery);
        $out = self::collectDtosFromRows($items, $searchQuery);
        // Apify often returns a non-empty list with an unexpected shape; fall back to iTunes when nothing maps.
        if ($out === []) {
            $out = self::collectDtosFromRows(self::searchItunesAsRows($searchQuery), $searchQuery);
        }

        return self::validatePodcastResults($out);
    }

    /**
     * @param  list<mixed>  $rows
     * @return list<ResultDTO>
     */
    private static function collectDtosFromRows(array $rows, SearchQueryDTO $searchQuery): array
    {
        $out = [];
        $seen = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $dto = self::rowToResultDTO($row);
            if ($dto === null) {
                continue;
            }
            $key = $dto->content->id;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $dto;
            if (count($out) >= max(1, min(200, $searchQuery->max_results))) {
                break;
            }
        }

        return $out;
    }

    /**
     * @param  list<ResultDTO>  $results
     * @return list<ResultDTO>
     */
    private static function validatePodcastResults(array $results): array
    {
        $ok = [];
        foreach ($results as $result) {
            if (! $result instanceof ResultDTO || $result->kind !== Kind::Podcast) {
                continue;
            }
            $c = $result->creator ?? null;
            $co = $result->content ?? null;
            if (! $c instanceof CreatorDTO || $c->id === '' || $c->name === '') {
                continue;
            }
            if (! $co instanceof ContentDTO || $co->id === '' || $co->name === '') {
                continue;
            }
            $ok[] = $result;
        }

        return $ok;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function searchApify(SearchQueryDTO $searchQuery): array
    {
        if (empty(trim((string) config('services.apify.token')))) {
            return [];
        }

        $max = max(1, min(200, $searchQuery->max_results));

        return ApifyPodcast::syncDatasetItems([
            'mode' => 'search',
            'searchTerms' => [trim($searchQuery->query)],
            'country' => $searchQuery->getStoreCountryCode(),
            'maxResults' => $max,
        ], 300);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function searchItunesAsRows(SearchQueryDTO $searchQuery): array
    {
        try {
            $response = Http::timeout(30)->get('https://itunes.apple.com/search', [
                'term' => $searchQuery->query,
                'entity' => 'podcast',
                'limit' => max(1, min(200, $searchQuery->max_results)),
                'country' => strtolower($searchQuery->getStoreCountryCode()),
            ]);
        } catch (\Throwable $e) {
            Log::warning('iTunes podcast search failed: '.$e->getMessage());

            return [];
        }
        if (! $response->successful()) {
            return [];
        }
        $json = $response->json();
        $results = $json['results'] ?? [];

        return array_map(fn ($r) => is_array($r) ? $r : (array) $r, $results);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private static function rowToResultDTO(array $row): ?ResultDTO
    {
        $appleId = self::stringFromRow($row, [
            'podcastId', 'collectionId', 'trackId', 'id', 'applePodcastId', 'apple_podcast_id',
        ]);
        if ($appleId === null || $appleId === '') {
            return null;
        }
        $appleId = (string) $appleId;
        $title = self::stringFromRow($row, ['title', 'trackName', 'collectionName', 'name']);
        if ($title === null || $title === '') {
            return null;
        }
        $creatorExternalId = self::stringFromRow($row, ['artistId', 'artist_id']) ?? $appleId;
        $creatorExternalId = (string) $creatorExternalId;
        $author = self::stringFromRow($row, ['author', 'artistName', 'artist', 'feedAuthor']) ?? $title;
        $feed = self::stringFromRow($row, ['feedUrl', 'rssUrl', 'rss_url', 'feed']);
        $art = self::stringFromRow($row, [
            'artworkUrl600', 'artworkUrl512', 'artworkUrl100', 'artworkUrl60', 'artworkUrl30',
            'thumbnail', 'thumbnailUrl', 'image', 'artwork',
        ]) ?? '';

        $result = new ResultDTO(Platform::Podcasts, Kind::Podcast);
        $result->creator = new CreatorDTO(Platform::Podcasts, $creatorExternalId);
        $result->creator->name = $author;
        $result->creator->avatar_url = $art ?: null;
        $result->creator->description = self::stringFromRow($row, ['description', 'summary']);

        $result->content = new ContentDTO(Platform::Podcasts, Kind::Podcast, $appleId);
        $result->content->name = $title;
        $result->content->description = self::stringFromRow($row, ['description', 'summary']);
        $result->content->thumbnail_url = $art;
        $result->content->rss_url = $feed ?? '';
        $result->content->creator_id = $creatorExternalId;

        return $result;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    private static function stringFromRow(array $row, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (! array_key_exists($k, $row)) {
                continue;
            }
            $v = $row[$k];
            if ($v === null || $v === '') {
                continue;
            }
            if (is_string($v)) {
                return trim($v) !== '' ? trim($v) : null;
            }
            if (is_numeric($v)) {
                return (string) $v;
            }
        }

        return null;
    }

    public static function syncEpisodesFromRss(Podcast $podcast, int $limit = 100): void
    {
        $url = $podcast->rss_url;
        if ($url === null || trim((string) $url) === '' || ! str_starts_with((string) $url, 'http')) {
            return;
        }
        try {
            $xml = simplexml_load_file($url);
        } catch (\Throwable $e) {
            Log::warning('Podcast RSS load failed: '.$e->getMessage(), ['rss' => $url]);

            return;
        }
        if ($xml === false) {
            return;
        }
        $channel = $xml->channel;
        if ($channel === null) {
            return;
        }
        $items = $channel->item;
        if ($items === null) {
            return;
        }
        $count = 0;
        foreach ($items as $item) {
            if ($count >= $limit) {
                break;
            }
            /** @var SimpleXMLElement $item */
            $title = trim((string) $item->title);
            if ($title === '') {
                continue;
            }
            $guid = isset($item->guid) ? trim((string) $item->guid) : $title;
            $slug = 'ep-'.substr(hash('sha256', $podcast->id.'|'.$guid), 0, 24);
            $audioEl = $item->enclosure;
            $audioUrl = $audioEl ? (string) $audioEl['url'] : (string) $item->link;
            if ($audioUrl === '') {
                continue;
            }
            $itunes = $item->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
            $durationRaw = isset($itunes->duration) ? (string) $itunes->duration : '0';
            $durationSeconds = self::normalizeDurationSeconds($durationRaw);
            $thumb = '';
            if (isset($itunes->image['href'])) {
                $thumb = (string) $itunes->image['href'];
            }
            if ($thumb === '' && $podcast->thumbnail_url) {
                $thumb = (string) $podcast->thumbnail_url;
            }
            $pub = null;
            if (isset($item->pubDate)) {
                try {
                    $pub = Carbon::parse((string) $item->pubDate);
                } catch (\Throwable) {
                    $pub = null;
                }
            }
            $desc = isset($item->description) ? strip_tags((string) $item->description) : null;
            $thumbFinal = $thumb !== '' ? $thumb : ($podcast->thumbnail_url ?: 'https://picsum.photos/seed/'.$slug.'/400/400');

            PodcastEpisode::updateOrCreate(
                ['podcast_id' => $podcast->id, 'slug' => $slug],
                [
                    'title' => Str::limit($title, 250, ''),
                    'audio_url' => $audioUrl,
                    'description' => $desc,
                    'duration' => (string) $durationSeconds,
                    'time_published' => $pub,
                    'thumbnail_url' => $thumbFinal,
                ]
            );
            $count++;
        }
    }

    private static function normalizeDurationSeconds(string $durationRaw): int
    {
        $durationRaw = trim($durationRaw);
        if ($durationRaw === '') {
            return 0;
        }
        if (is_numeric($durationRaw)) {
            return max(0, (int) $durationRaw);
        }
        if (function_exists('convertTimeToSeconds')) {
            return max(0, (int) convertTimeToSeconds($durationRaw));
        }
        $parts = array_map('intval', explode(':', $durationRaw));

        if (count($parts) === 3) {
            return $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
        }
        if (count($parts) === 2) {
            return $parts[0] * 60 + $parts[1];
        }

        return 0;
    }
}
