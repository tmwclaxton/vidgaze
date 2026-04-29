<?php

namespace App\Helpers;

/**
 * Maps inputs/outputs between {@see PlatformAPIs\YouTube} (streamers-style) and Apify `apidojo~youtube-scraper`.
 *
 * Streamers uses `searchQueries`, `maxResults`, `startUrls` as `[['url' => '…']]`, and `sortVideosBy`.
 * Apidojo uses `keywords`, `maxItems`, string `startUrls`, and returns nested `channel` plus `views` (not `viewCount`).
 */
class ApifyYoutubeActorAdapter
{
    public static function usesApidojoInputShape(): bool
    {
        $actor = (string) config('services.apify.youtube_actor', '');

        return str_contains(strtolower($actor), 'apidojo');
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalizeInputBeforeSync(array $input): array
    {
        if (! self::usesApidojoInputShape()) {
            return $input;
        }

        $out = $input;

        if (isset($out['searchQueries']) && is_array($out['searchQueries'])) {
            $out['keywords'] = $out['searchQueries'];
            unset($out['searchQueries']);
        }

        if (isset($out['maxResults'])) {
            $out['maxItems'] = (int) $out['maxResults'];
            unset($out['maxResults']);
        }

        unset($out['sortVideosBy']);

        if (isset($out['startUrls']) && is_array($out['startUrls'])) {
            $urls = [];
            foreach ($out['startUrls'] as $entry) {
                if (is_string($entry) && $entry !== '') {
                    $urls[] = self::apidojoAdjustChannelRootUrl($entry);
                } elseif (is_array($entry) && ! empty($entry['url']) && is_string($entry['url'])) {
                    $urls[] = self::apidojoAdjustChannelRootUrl($entry['url']);
                }
            }
            $out['startUrls'] = $urls;
            if ($urls !== [] && empty($out['keywords'])) {
                $max = (int) ($out['maxItems'] ?? 0);
                $floor = self::apidojoStartUrlsMinMaxItems($urls);
                if ($max < $floor) {
                    $out['maxItems'] = $floor;
                }
            }
        }

        $out['gl'] = $out['gl'] ?? 'us';
        $out['hl'] = $out['hl'] ?? 'en';

        return $out;
    }

    /**
     * @param  list<string>  $urls
     */
    private static function apidojoStartUrlsMinMaxItems(array $urls): int
    {
        foreach ($urls as $u) {
            if (! is_string($u)) {
                continue;
            }
            if (preg_match('#[?&]v=#', $u) || str_contains($u, 'youtu.be/')) {
                return 10;
            }
        }

        return 25;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function normalizeRowsAfterSync(array $rows): array
    {
        if (! self::usesApidojoInputShape()) {
            return $rows;
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (! empty($row['noResults']) && ! empty($row['error'])) {
                continue;
            }
            $out[] = self::flattenApidojoVideoRow($row);
        }

        return $out;
    }

    /**
     * Bare `…/channel/UC…` or `…/@handle` URLs return a synthetic error row from apidojo; use `/videos` like the site tab.
     */
    public static function apidojoAdjustChannelRootUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $url;
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path)) {
            return $url;
        }
        $path = '/'.trim($path, '/');
        if (preg_match('#^/channel/[^/]+$#i', $path)) {
            return self::stripTrailingSlash($url).'/videos';
        }
        if (preg_match('#^/@[^/]+$#i', $path)) {
            return self::stripTrailingSlash($url).'/videos';
        }

        return $url;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function flattenApidojoVideoRow(array $item): array
    {
        if (! empty($item['channel']) && is_array($item['channel'])) {
            $ch = $item['channel'];
            if (empty($item['channelId']) && ! empty($ch['id']) && is_string($ch['id'])) {
                $item['channelId'] = $ch['id'];
            }
            if (empty($item['channelName']) && ! empty($ch['name']) && is_string($ch['name'])) {
                $item['channelName'] = $ch['name'];
            }
            if (empty($item['channelUrl']) && ! empty($ch['url']) && is_string($ch['url'])) {
                $item['channelUrl'] = $ch['url'];
            }
            if (empty($item['uploaderName']) && ! empty($ch['name']) && is_string($ch['name'])) {
                $item['uploaderName'] = $ch['name'];
            }
        }

        if (empty($item['text']) && ! empty($item['description']) && is_string($item['description'])) {
            $item['text'] = $item['description'];
        }

        if (! array_key_exists('viewCount', $item) && isset($item['views'])) {
            $item['viewCount'] = $item['views'];
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $summary  Decoded SUMMARY.json from {@see CompareYoutubeApifyActors}
     * @return array<string, mixed>
     */
    public static function gapReportFromMatrixSummary(array $summary): array
    {
        $cases = $summary['cases'] ?? [];

        return [
            'generated_from' => 'youtube:compare-apify-actors SUMMARY.json',
            'youtube_php_paths' => [
                'searchVideos' => [
                    'verdict' => 'compatible',
                    'detail' => 'Map searchQueries→keywords, maxResults→maxItems; flatten nested channel on output.',
                ],
                'searchCreators' => [
                    'verdict' => 'compatible',
                    'detail' => 'Same as searchVideos; creator fields come from flattened channel.*.',
                ],
                'getVideoOrStream' => [
                    'verdict' => 'compatible',
                    'detail' => 'Live test returned watch?v= rows; README discourages single-URL runs but API succeeded.',
                ],
                'getCreatorVideosBeforeDate' => [
                    'verdict' => 'compatible',
                    'detail' => 'Already uses /channel/…/videos; sortVideosBy dropped for apidojo (tab default ordering).',
                ],
                'getCreators' => [
                    'verdict' => 'compatible_with_adapter',
                    'detail' => 'Bare channel or @ URLs are rewritten to /videos; gl/hl default to us/en; channel-tab fetches use maxItems floor 25 (apidojo returned C020 with 10).',
                ],
            ],
            'matrix_case_notes' => [
                'channel_home_uc_apidojo' => $cases['channel_home_uc']['apidojo']['first_item_keys'] ?? [],
                'channel_home_handle_apidojo' => $cases['channel_home_handle']['apidojo']['first_item_keys'] ?? [],
            ],
            'residual_risks' => [
                'Channel avatars: apidojo video rows may omit channelAvatarUrl; existing enrichShortYoutubeChannelMetadata may still leave blanks.',
                'sortVideosBy NEWEST is not sent to apidojo; rely on YouTube /videos ordering.',
                'Actor README min-10 rule is not strictly enforced by API (maxItems=5 search succeeded in matrix).',
                'Channel listing calls use a higher maxItems floor (25) than watch-URL batches (10), increasing pay-per-result cost for getCreators / getCreatorVideos.',
            ],
        ];
    }

    private static function stripTrailingSlash(string $url): string
    {
        if (str_ends_with($url, '/')) {
            return rtrim($url, '/');
        }

        return $url;
    }
}
