<?php

namespace App\Helpers\Scraping;

use Carbon\Carbon;

/**
 * Parses public YouTube pages (search results and watch pages) fetched via
 * crawl4ai, using the ytInitialData / ytInitialPlayerResponse JSON that
 * YouTube embeds in the HTML, plus the public channel RSS feed.
 */
class YouTubeParser
{
    public static function extractJson(string $html, string $var): ?array
    {
        if (!preg_match_all('/' . preg_quote($var, '/') . '"?\]?\s*=\s*\{/', $html, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        foreach ($m[0] as [$match, $offset]) {
            $start = $offset + strlen($match) - 1;
            $json = self::balancedObject($html, $start);
            if ($json !== null) {
                $data = json_decode($json, true);
                if (is_array($data)) return $data;
            }
        }
        return null;
    }

    private static function balancedObject(string $s, int $start): ?string
    {
        $depth = 0; $inStr = false; $esc = false; $len = strlen($s);
        for ($i = $start; $i < $len; $i++) {
            $c = $s[$i];
            if ($inStr) {
                if ($esc) { $esc = false; }
                elseif ($c === '\\') { $esc = true; }
                elseif ($c === '"') { $inStr = false; }
                continue;
            }
            if ($c === '"') { $inStr = true; }
            elseif ($c === '{') { $depth++; }
            elseif ($c === '}') { if (--$depth === 0) return substr($s, $start, $i - $start + 1); }
        }
        return null;
    }

    /** Collect every value stored under $key anywhere in $data. */
    public static function findAll(mixed $data, string $key, array &$out = []): array
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if ($k === $key) $out[] = $v;
                if (is_array($v)) self::findAll($v, $key, $out);
            }
        }
        return $out;
    }

    private static function runsText(?array $node): ?string
    {
        if (!$node) return null;
        if (isset($node['simpleText'])) return $node['simpleText'];
        if (isset($node['runs'])) return implode('', array_column($node['runs'], 'text'));
        return null;
    }

    /** "15 hr ago", "2 weeks ago", "Streamed 3 days ago" -> Carbon */
    public static function relativeTime(?string $text): Carbon
    {
        if ($text && preg_match('/(\d+)\s*(sec|second|min|minute|hr|hour|day|week|wk|month|mo|year|yr)s?\b/i', $text, $m)) {
            $map = ['sec' => 'seconds', 'second' => 'seconds', 'min' => 'minutes', 'minute' => 'minutes', 'hr' => 'hours', 'hour' => 'hours',
                'day' => 'days', 'week' => 'weeks', 'wk' => 'weeks', 'month' => 'months', 'mo' => 'months', 'year' => 'years', 'yr' => 'years'];
            return Carbon::now()->sub((int) $m[1], $map[strtolower($m[2])]);
        }
        return Carbon::now();
    }

    /**
     * Search results page: videos with their channel.
     */
    public static function parseSearch(string $html, int $limit = 5): array
    {
        $data = self::extractJson($html, 'ytInitialData');
        if (!$data) return [];
        $out = [];
        foreach (self::findAll($data, 'videoRenderer') as $v) {
            $id = $v['videoId'] ?? null;
            $owner = $v['ownerText']['runs'][0] ?? $v['longBylineText']['runs'][0] ?? null;
            $channelId = $owner['navigationEndpoint']['browseEndpoint']['browseId'] ?? null;
            $title = self::runsText($v['title'] ?? null);
            if (!$id || !$channelId || !$title || isset($out[$id])) continue;
            $length = $v['lengthText']['simpleText'] ?? null;
            $snippet = $v['detailedMetadataSnippets'][0]['snippetText'] ?? null;
            $avatar = self::findAll($v['channelThumbnailSupportedRenderers'] ?? [], 'url')[0] ?? null;
            $out[$id] = [
                'videoId' => $id,
                'title' => $title,
                'description' => self::runsText($snippet) ?? '',
                'duration' => $length ? RumbleParser::colonToSeconds($length) : 0,
                'is_live' => $length === null,
                'publish_time' => self::relativeTime($v['publishedTimeText']['simpleText'] ?? null),
                'thumbnail' => end($v['thumbnail']['thumbnails'])['url'] ?? "https://i.ytimg.com/vi/$id/hqdefault.jpg",
                'channelId' => $channelId,
                'channelName' => $owner['text'] ?? $channelId,
                'channelAvatar' => $avatar,
            ];
            if (count($out) >= $limit) break;
        }
        return array_values($out);
    }

    /**
     * Watch page: one video and its owner.
     */
    public static function parseWatch(string $html): ?array
    {
        $player = self::extractJson($html, 'ytInitialPlayerResponse');
        $vd = $player['videoDetails'] ?? null;
        if (!$vd || empty($vd['videoId']) || empty($vd['channelId'])) return null;
        $mf = $player['microformat']['playerMicroformatRenderer'] ?? [];
        $initial = self::extractJson($html, 'ytInitialData') ?? [];
        $owner = self::findAll($initial, 'videoOwnerRenderer')[0] ?? [];
        $thumbs = $owner['thumbnail']['thumbnails'] ?? [];
        $publish = $mf['publishDate'] ?? $mf['uploadDate'] ?? null;
        $live = !empty($vd['isLive']) || (!empty($vd['isLiveContent']) && (int) ($vd['lengthSeconds'] ?? 0) === 0);
        return [
            'videoId' => $vd['videoId'],
            'title' => $vd['title'] ?? '',
            'description' => $vd['shortDescription'] ?? '',
            'duration' => (int) ($vd['lengthSeconds'] ?? 0),
            'is_live' => $live,
            'publish_time' => $publish ? Carbon::parse($publish) : Carbon::now(),
            'thumbnail' => end($vd['thumbnail']['thumbnails'])['url'] ?? "https://i.ytimg.com/vi/{$vd['videoId']}/hqdefault.jpg",
            'channelId' => $vd['channelId'],
            'channelName' => $vd['author'] ?? $vd['channelId'],
            'channelAvatar' => $thumbs ? end($thumbs)['url'] : null,
        ];
    }

    /**
     * Channel /videos page (fetched with crawl4ai's site_profile "youtube" so
     * the EU consent wall is skipped): channel details, banner and the first
     * page of uploads with durations. Returns null when the page is not a
     * channel page (for example the consent interstitial).
     */
    public static function parseChannel(string $html): ?array
    {
        $data = self::extractJson($html, 'ytInitialData');
        $meta = $data['metadata']['channelMetadataRenderer'] ?? null;
        if (!$meta || empty($meta['externalId'])) return null;
        $channelId = $meta['externalId'];

        $header = self::findAll($data['header'] ?? [], 'pageHeaderViewModel')[0] ?? [];
        $bannerSources = self::findAll($header['banner'] ?? [], 'sources')[0] ?? [];
        if (!$bannerSources) {
            // older c4TabbedHeaderRenderer layout
            $bannerSources = $data['header']['c4TabbedHeaderRenderer']['banner']['thumbnails'] ?? [];
        }
        $banner = $bannerSources ? self::largest($bannerSources) : null;
        $avatars = $meta['avatar']['thumbnails'] ?? [];
        $avatar = $avatars ? self::largest($avatars) : null;

        $videos = [];
        foreach (self::findAll($data['contents'] ?? [], 'lockupViewModel') as $l) {
            $id = $l['contentId'] ?? null;
            if (!$id || ($l['contentType'] ?? 'LOCKUP_CONTENT_TYPE_VIDEO') !== 'LOCKUP_CONTENT_TYPE_VIDEO' || isset($videos[$id])) continue;
            $md = $l['metadata']['lockupMetadataViewModel'] ?? [];
            $badges = self::findAll($l['contentImage'] ?? [], 'thumbnailBadgeViewModel');
            $length = null;
            foreach ($badges as $b) {
                if (isset($b['text']) && preg_match('/^\d+(:\d{2}){1,2}$/', $b['text'])) { $length = $b['text']; break; }
            }
            $when = null;
            foreach ($md['metadata']['contentMetadataViewModel']['metadataRows'] ?? [] as $row) {
                foreach ($row['metadataParts'] ?? [] as $part) {
                    $label = $part['accessibilityLabel'] ?? $part['text']['content'] ?? '';
                    if (preg_match('/\bago\b/i', $label)) $when = $label;
                }
            }
            $thumbs = self::findAll($l['contentImage'] ?? [], 'sources')[0] ?? [];
            $videos[$id] = self::channelVideo($id, $md['title']['content'] ?? '', $length, $when, $thumbs ? self::largest($thumbs) : null, $channelId, $meta, $avatar);
        }
        // older layout: richItemRenderer > videoRenderer
        foreach (self::findAll($data['contents'] ?? [], 'videoRenderer') as $v) {
            $id = $v['videoId'] ?? null;
            if (!$id || isset($videos[$id])) continue;
            $thumbs = $v['thumbnail']['thumbnails'] ?? [];
            $videos[$id] = self::channelVideo($id, self::runsText($v['title'] ?? null) ?? '', $v['lengthText']['simpleText'] ?? null,
                $v['publishedTimeText']['simpleText'] ?? null, $thumbs ? end($thumbs)['url'] : null, $channelId, $meta, $avatar);
        }

        return [
            'channelId' => $channelId,
            'name' => $meta['title'] ?? $channelId,
            'description' => $meta['description'] ?? '',
            'avatar' => $avatar,
            'banner' => $banner,
            'videos' => array_values($videos),
        ];
    }

    private static function channelVideo(string $id, string $title, ?string $length, ?string $when, ?string $thumb, string $channelId, array $meta, ?string $avatar): array
    {
        return [
            'videoId' => $id,
            'title' => $title,
            'description' => '',
            'duration' => $length ? RumbleParser::colonToSeconds($length) : 0,
            'is_live' => $length === null,
            'publish_time' => self::relativeTime($when),
            'thumbnail' => $thumb ?: "https://i.ytimg.com/vi/$id/hqdefault.jpg",
            'channelId' => $channelId,
            'channelName' => $meta['title'] ?? $channelId,
            'channelAvatar' => $avatar,
        ];
    }

    private static function largest(array $sources): ?string
    {
        usort($sources, fn ($a, $b) => ($b['width'] ?? 0) <=> ($a['width'] ?? 0));
        return $sources[0]['url'] ?? null;
    }

    /**
     * Public channel RSS feed (https://www.youtube.com/feeds/videos.xml?channel_id=...).
     * Returns ['name' => ..., 'videos' => [...]].
     */
    public static function parseFeed(string $xml): ?array
    {
        $prev = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        if (!$feed) return null;
        $videos = [];
        foreach ($feed->entry as $entry) {
            $yt = $entry->children('http://www.youtube.com/xml/schemas/2015');
            $media = $entry->children('http://search.yahoo.com/mrss/');
            $id = (string) $yt->videoId;
            if ($id === '') continue;
            $videos[] = [
                'videoId' => $id,
                'title' => (string) $entry->title,
                'description' => (string) ($media->group->description ?? ''),
                'publish_time' => Carbon::parse((string) $entry->published),
                'thumbnail' => "https://i.ytimg.com/vi/$id/hqdefault.jpg",
            ];
        }
        return ['name' => (string) ($feed->author->name ?? $feed->title), 'videos' => $videos];
    }
}
