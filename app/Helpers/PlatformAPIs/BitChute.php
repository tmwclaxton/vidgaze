<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Audience;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\FirecrawlClient;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\Tools;
use App\Helpers\VideoDurationParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BitChute implements iIsPlatform, iSearchable
{
    private const GENERIC_BC_SHARE_THUMB = 'https://bcfiles.bitchute.com/img/share/bc-sharing.webp';

    public static function getPlatform(): Platform
    {
        return Platform::BitChute;
    }

    public static function search(SearchQueryDTO $searchQuery): array
    {
        $fc = FirecrawlClient::make();
        if ($fc !== null) {
            $fromFc = self::searchViaFirecrawl($fc, $searchQuery);
            if ($fromFc !== []) {
                return $fromFc;
            }
        }

        return [];
    }

    /**
     * @return list<ResultDTO>
     */
    private static function searchViaFirecrawl(FirecrawlClient $fc, SearchQueryDTO $searchQueryDTO): array
    {
        $query = trim($searchQueryDTO->query);
        if ($query === '') {
            return [];
        }

        $limit = min(20, max(3, $searchQueryDTO->max_results));
        $hits = self::firecrawlVideoHits($fc, $query, $limit);
        if ($hits === []) {
            return [];
        }

        $out = [];
        $seen = [];
        foreach ($hits as $hit) {
            $url = (string) ($hit['url'] ?? '');
            $vid = self::videoIdFromUrl($url);
            if ($vid === null || isset($seen[$vid])) {
                continue;
            }
            $seen[$vid] = true;

            $resultDTO = new ResultDTO(Platform::BitChute, Kind::Video);
            $contentDTO = new ContentDTO(Platform::BitChute, Kind::Video, $vid);
            $contentDTO->name = (string) ($hit['title'] ?? 'BitChute video');
            $contentDTO->description = (string) ($hit['description'] ?? '');
            $contentDTO->thumbnail_url = '';
            $contentDTO->publish_time = Carbon::now();
            $contentDTO->duration = '0';
            $contentDTO->creator_id = 'bitchute';
            $contentDTO->tags = [];
            $contentDTO->audience = Audience::ALL;

            $creatorDTO = new CreatorDTO(Platform::BitChute, 'bitchute');
            $creatorDTO->name = 'BitChute';

            $resultDTO->content = $contentDTO;
            $resultDTO->creator = $creatorDTO;

            $out[] = $resultDTO;
        }

        if ($out !== []) {
            $enrichCap = min(count($out), max(5, (int) $searchQueryDTO->max_results));
            self::enrichTopFirecrawlResults($fc, $out, $enrichCap);
        }

        return Tools::validateDTOs($out);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function firecrawlVideoHits(FirecrawlClient $fc, string $query, int $limit): array
    {
        $attempts = [
            'site:bitchute.com/video '.$query,
            'site:www.bitchute.com/video '.$query,
            'site:bitchute.com '.$query,
        ];
        foreach ($attempts as $q) {
            $hits = $fc->search($q, $limit, []);
            if ($hits !== []) {
                return $hits;
            }
        }

        return [];
    }

    /**
     * @param  list<ResultDTO>  $results
     */
    private static function enrichTopFirecrawlResults(FirecrawlClient $fc, array &$results, int $max): void
    {
        foreach (array_slice($results, 0, $max) as $resultDTO) {
            $content = $resultDTO->content;
            if (! $content instanceof ContentDTO) {
                continue;
            }
            $vid = $content->id;
            $page = $fc->scrape('https://www.bitchute.com/video/'.$vid.'/', ['markdown', 'links', 'html'], 120);
            if ($page === null) {
                continue;
            }
            $meta = is_array($page['metadata'] ?? null) ? $page['metadata'] : [];
            $html = (string) ($page['html'] ?? '');
            $videoUrl = 'https://www.bitchute.com/video/'.$vid.'/';
            if (strlen($html) < 200) {
                $html = self::fetchHtmlFallback($videoUrl) ?: $html;
            }
            $title = self::metaTitle($meta);
            if ($title !== '' && ! self::isGenericBitchuteTitle($title)) {
                $content->name = self::stripBitchuteTitleSuffix($title);
            }
            if (is_string($meta['ogImage'] ?? null)) {
                $og = self::absoluteBitchuteAssetUrl((string) $meta['ogImage']);
                if ($og !== '' && $og !== self::GENERIC_BC_SHARE_THUMB) {
                    $content->thumbnail_url = $og;
                }
            }
            $md = (string) ($page['markdown'] ?? '');

            $extracted = self::extractVideoSemantics($html, $md, $meta);
            $bestDesc = self::longestMeaningfulDescription(
                (string) ($content->description ?? ''),
                $extracted['description'] ?? '',
                self::longestFirecrawlMetaDescription($meta),
                self::htmlMetaDescriptionContent($html),
                self::markdownDescriptionBlock($md),
            );
            if ($bestDesc !== '') {
                $content->description = $bestDesc;
            }

            $dur = $extracted['duration_seconds'] ?? null;
            if ($dur === null || $dur <= 0) {
                $dur = self::parseLooseDuration((string) ($meta['duration'] ?? ''));
            }
            if (($dur === null || $dur <= 0) && $md !== '' && preg_match('/(?:Duration|duration)\s*:\s*([^\n]+)/', $md, $m)) {
                $dur = self::parseLooseDuration(trim($m[1]));
            }
            if ($dur !== null && $dur > 0) {
                $content->duration = (string) $dur;
            }

            $slugPage = $page;
            $slugPage['html'] = $html;
            $channelSlug = $extracted['channel_slug'] ?? self::extractChannelSlug($slugPage);
            if ($channelSlug !== null) {
                $resultDTO->creator = new CreatorDTO(Platform::BitChute, $channelSlug);
                $resultDTO->creator->name = self::channelDisplayNameFromSlug($channelSlug);
                $resultDTO->creator->avatar_url = null;
                $content->creator_id = $channelSlug;

                $chUrl = 'https://www.bitchute.com/channel/'.$channelSlug.'/';
                $ch = $fc->scrape($chUrl, ['metadata', 'html'], 90);
                $chMeta = is_array($ch) && is_array($ch['metadata'] ?? null) ? $ch['metadata'] : [];
                $chHtml = is_array($ch) ? (string) ($ch['html'] ?? '') : '';
                if (strlen($chHtml) < 200) {
                    $chHtml = self::fetchHtmlFallback($chUrl) ?: $chHtml;
                }
                $displayName = self::channelDisplayNameFromMetaAndHtml($chMeta, $chHtml, $channelSlug);
                $resultDTO->creator->name = $displayName;

                $avatar = self::bestChannelAvatarUrl($chMeta, $chHtml);
                if ($avatar !== null) {
                    $resultDTO->creator->avatar_url = $avatar;
                }
                if (
                    $resultDTO->creator->avatar_url === null
                    && is_string($content->thumbnail_url ?? null)
                    && trim($content->thumbnail_url) !== ''
                    && trim($content->thumbnail_url) !== self::GENERIC_BC_SHARE_THUMB
                ) {
                    // BitChute often leaves og:image empty on channel pages; video thumb is better than nothing.
                    $resultDTO->creator->avatar_url = self::absoluteBitchuteAssetUrl($content->thumbnail_url);
                }
            }
        }
    }

    /**
     * Direct fetch when Firecrawl returns an empty body (common on BitChute channel pages).
     */
    private static function fetchHtmlFallback(string $url): string
    {
        try {
            $response = Http::timeout(25)
                ->connectTimeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($url);
            if ($response->successful()) {
                $body = $response->body();

                return is_string($body) && strlen($body) > 200 ? $body : '';
            }
        } catch (\Throwable $e) {
            Log::debug('BitChute HTML fallback failed: '.$e->getMessage(), ['url' => $url]);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $page
     */
    private static function extractChannelSlug(array $page): ?string
    {
        $html = (string) ($page['html'] ?? '');
        if ($html !== '' && preg_match('#https?://(?:www\.)?bitchute\.com/channel/([a-zA-Z0-9_-]+)#i', $html, $m)) {
            return $m[1];
        }
        if (isset($page['links']) && is_array($page['links'])) {
            foreach ($page['links'] as $link) {
                $slug = self::channelSlugFromUrl((string) $link);
                if ($slug !== null) {
                    return $slug;
                }
            }
        }
        $md = (string) ($page['markdown'] ?? '');
        if (preg_match('#bitchute\.com/channel/([a-zA-Z0-9_-]+)#i', $md, $m)) {
            return $m[1];
        }

        return null;
    }

    private static function channelSlugFromUrl(string $url): ?string
    {
        if (preg_match('#https?://(?:www\.)?bitchute\.com/channel/([a-zA-Z0-9_-]+)#i', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    private static function channelDisplayNameFromSlug(string $slug): string
    {
        $t = str_replace(['_', '-'], ' ', $slug);
        $t = trim($t);

        return $t !== '' ? $t : $slug;
    }

    private static function videoIdFromUrl(string $url): ?string
    {
        if (preg_match('~(?:https?://)?(?:www\.)?bitchute\.com/video/([a-zA-Z0-9_-]{8,})(?:/|$|\?|#)~i', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    private static function isGenericBitchuteTitle(string $title): bool
    {
        return strcasecmp(trim($title), 'Bitchute') === 0
            || strcasecmp(trim($title), 'BitChute') === 0;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private static function metaTitle(array $meta): string
    {
        $t = $meta['title'] ?? '';
        if (is_string($t)) {
            return $t;
        }
        if (is_array($t)) {
            return trim((string) ($t[0] ?? ''));
        }

        return '';
    }

    private static function parseLooseDuration(string $raw): ?int
    {
        return VideoDurationParser::secondsFromDisplayString(trim($raw));
    }

    private static function stripBitchuteTitleSuffix(string $title): string
    {
        $t = trim($title);

        return (string) preg_replace('/\s*[-|—]\s*Bit[cC]hute\s*$/i', '', $t);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{duration_seconds: ?int, description: string, channel_slug: ?string}
     */
    private static function extractVideoSemantics(string $html, string $md, array $meta): array
    {
        $bestDesc = '';
        $duration = null;
        $slug = null;

        foreach (self::parseLdJsonBlocks($html) as $doc) {
            foreach (self::extractVideoObjectsFromLdDocument($doc) as $vo) {
                if ($duration === null && isset($vo['duration'])) {
                    $duration = self::normalizeDurationValue($vo['duration']);
                }
                $d = $vo['description'] ?? '';
                if (is_string($d)) {
                    $decoded = trim(html_entity_decode($d, ENT_QUOTES | ENT_HTML5));
                    if (strlen($decoded) > strlen($bestDesc)) {
                        $bestDesc = $decoded;
                    }
                }
                if ($slug === null) {
                    $slug = self::channelSlugFromLdAuthor($vo['author'] ?? null)
                        ?? self::channelSlugFromLdAuthor($vo['publisher'] ?? null);
                }
            }
        }

        return [
            'duration_seconds' => $duration,
            'description' => $bestDesc,
            'channel_slug' => $slug,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function parseLdJsonBlocks(string $html): array
    {
        if ($html === '') {
            return [];
        }
        $docs = [];
        if (! preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $blocks)) {
            return [];
        }
        foreach ($blocks[1] as $raw) {
            $json = json_decode(trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5)), true);
            if (is_array($json)) {
                $docs[] = $json;
            }
        }

        return $docs;
    }

    /**
     * @param  array<string, mixed>  $doc
     * @return list<array<string, mixed>>
     */
    private static function extractVideoObjectsFromLdDocument(array $doc): array
    {
        $out = [];
        self::collectVideoObjectsFromLd($doc, $out);

        return $out;
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $node
     * @param  list<array<string, mixed>>  $into
     */
    private static function collectVideoObjectsFromLd(array $node, array &$into): void
    {
        if (($node['@type'] ?? null) === 'VideoObject' || (is_array($node['@type'] ?? null) && in_array('VideoObject', $node['@type'], true))) {
            $into[] = $node;
        }
        if (isset($node['@graph']) && is_array($node['@graph'])) {
            foreach ($node['@graph'] as $item) {
                if (is_array($item)) {
                    self::collectVideoObjectsFromLd($item, $into);
                }
            }
        }
        foreach ($node as $key => $value) {
            if ($key === '@graph' || ! is_array($value)) {
                continue;
            }
            if (isset($value['@type'])) {
                self::collectVideoObjectsFromLd($value, $into);
            }
        }
    }

    private static function normalizeDurationValue(mixed $v): ?int
    {
        if (is_int($v)) {
            return $v > 0 ? $v : null;
        }
        if (is_string($v)) {
            $v = trim($v);
            if ($v !== '' && ctype_digit($v)) {
                $n = (int) $v;

                return $n > 0 ? $n : null;
            }
            if (preg_match('/^P/i', $v)) {
                return self::parseIso8601Duration($v);
            }

            return self::parseLooseDuration($v);
        }

        return null;
    }

    private static function parseIso8601Duration(string $s): ?int
    {
        $s = trim($s);
        if ($s === '' || ! preg_match('/^P/i', $s)) {
            return null;
        }
        if (preg_match('/P(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?)?$/i', $s, $m)) {
            $d = isset($m[1]) ? (int) $m[1] : 0;
            $h = isset($m[2]) ? (int) $m[2] : 0;
            $min = isset($m[3]) ? (int) $m[3] : 0;
            $sec = isset($m[4]) ? (int) round((float) $m[4]) : 0;

            $total = $d * 86400 + $h * 3600 + $min * 60 + $sec;

            return $total > 0 ? $total : null;
        }

        return null;
    }

    private static function channelSlugFromLdAuthor(mixed $author): ?string
    {
        if (is_string($author)) {
            return self::channelSlugFromUrl($author);
        }
        if (! is_array($author)) {
            return null;
        }
        foreach (['url', '@id', 'sameAs'] as $k) {
            if (! empty($author[$k]) && is_string($author[$k])) {
                $slug = self::channelSlugFromUrl($author[$k]);
                if ($slug !== null) {
                    return $slug;
                }
            }
        }

        return null;
    }

    private static function longestMeaningfulDescription(string ...$parts): string
    {
        $best = '';
        foreach ($parts as $p) {
            $t = trim(html_entity_decode(strip_tags($p), ENT_QUOTES | ENT_HTML5));
            if (strlen($t) < 8) {
                continue;
            }
            if (strlen($t) > strlen($best)) {
                $best = $t;
            }
        }

        return $best;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private static function longestFirecrawlMetaDescription(array $meta): string
    {
        $best = '';
        foreach (['description', 'ogDescription', 'twitterDescription'] as $k) {
            if (! isset($meta[$k]) || ! is_string($meta[$k])) {
                continue;
            }
            $t = trim($meta[$k]);
            if (strlen($t) > strlen($best)) {
                $best = $t;
            }
        }

        return $best;
    }

    private static function htmlMetaDescriptionContent(string $html): string
    {
        if ($html === '' || ! preg_match(
            '/<meta\s+name=["\']description["\']\s+content=["\']([^"\']*)["\']/i',
            $html,
            $m
        )) {
            return '';
        }

        return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
    }

    private static function markdownDescriptionBlock(string $md): string
    {
        if ($md === '') {
            return '';
        }
        if (preg_match(
            '/(?:^|\n)\s*Description\s*:\s*\n?(.*?)(?=\n\s*(?:Duration|Category|Views|Uploaded|Subscribe|Tags)\s*:|$)/isu',
            $md,
            $m
        )) {
            return trim($m[1]);
        }
        if (preg_match('/Description\s*:\s*(.+)/isu', $md, $m)) {
            return trim($m[1]);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private static function channelDisplayNameFromMetaAndHtml(array $meta, string $html, string $fallbackSlug): string
    {
        foreach ([
            self::metaTagContent($html, 'property', 'og:title'),
            self::metaTagContent($html, 'name', 'twitter:title'),
            self::metaTitle($meta),
            (string) ($meta['ogTitle'] ?? ''),
            (string) ($meta['og:title'] ?? ''),
        ] as $raw) {
            $t = self::stripBitchuteTitleSuffix(trim($raw));
            if ($t !== '' && ! self::isGenericBitchuteTitle($t)) {
                return $t;
            }
        }
        $ldName = self::channelNameFromLdJson($html);
        if ($ldName !== null && $ldName !== '') {
            return $ldName;
        }
        if ($html !== '' && preg_match('/<h1[^>]*>\s*([^<]+)\s*</i', $html, $m)) {
            $t = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
            if ($t !== '' && ! self::isGenericBitchuteTitle($t)) {
                return $t;
            }
        }

        return self::channelDisplayNameFromSlug($fallbackSlug);
    }

    private static function metaTagContent(string $html, string $attr, string $value): string
    {
        if ($html === '') {
            return '';
        }
        $q = preg_quote($value, '/');
        if (! preg_match('/<meta\s+'.preg_quote($attr, '/').'=["\']'.$q.'["\']\s+content=["\']([^"\']*)["\']/i', $html, $m)) {
            return '';
        }

        return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
    }

    private static function channelNameFromLdJson(string $html): ?string
    {
        $best = null;
        foreach (self::parseLdJsonBlocks($html) as $doc) {
            self::walkLdForName($doc, $best);
        }

        return $best;
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $node
     */
    private static function walkLdForName(mixed $node, ?string &$best): void
    {
        if (! is_array($node)) {
            return;
        }
        $types = $node['@type'] ?? null;
        $typeList = is_array($types) ? $types : ($types !== null ? [$types] : []);
        $interesting = count(array_intersect($typeList, ['Person', 'Organization', 'ProfilePage', 'WebSite'])) > 0;
        if ($interesting && isset($node['name']) && is_string($node['name'])) {
            $n = trim(html_entity_decode($node['name'], ENT_QUOTES | ENT_HTML5));
            if ($n !== '' && strlen($n) > strlen((string) $best)) {
                $best = $n;
            }
        }
        $url = $node['url'] ?? null;
        if (is_string($url) && str_contains($url, 'bitchute.com/channel') && isset($node['name']) && is_string($node['name'])) {
            $n = trim(html_entity_decode($node['name'], ENT_QUOTES | ENT_HTML5));
            if ($n !== '' && strlen($n) > strlen((string) $best)) {
                $best = $n;
            }
        }
        if (isset($node['@graph']) && is_array($node['@graph'])) {
            foreach ($node['@graph'] as $item) {
                self::walkLdForName($item, $best);
            }
        }
        foreach ($node as $k => $v) {
            if ($k === '@graph' || ! is_array($v)) {
                continue;
            }
            self::walkLdForName($v, $best);
        }
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private static function bestChannelAvatarUrl(array $meta, string $html): ?string
    {
        $candidates = [];
        foreach (['ogImage', 'twitterImage', 'image', 'og:image'] as $k) {
            if (! empty($meta[$k]) && is_string($meta[$k])) {
                $candidates[] = trim($meta[$k]);
            }
        }
        if ($html !== '') {
            foreach (['og:image', 'twitter:image'] as $prop) {
                $t = self::metaTagContent($html, 'property', $prop);
                if ($t !== '') {
                    $candidates[] = $t;
                }
            }
            $tw = self::metaTagContent($html, 'name', 'twitter:image');
            if ($tw !== '') {
                $candidates[] = $tw;
            }
            if (preg_match('/<img[^>]+class="[^"]*\bchannel-thumbnail\b[^"]*"[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
                $candidates[] = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
            }
        }
        foreach ($candidates as $u) {
            $u = self::absoluteBitchuteAssetUrl($u);
            if ($u !== '' && $u !== self::GENERIC_BC_SHARE_THUMB && str_contains($u, 'http')) {
                return $u;
            }
        }

        return null;
    }

    private static function absoluteBitchuteAssetUrl(string $u): string
    {
        $u = trim($u);
        if ($u === '') {
            return '';
        }
        if (str_starts_with($u, '//')) {
            return 'https:'.$u;
        }
        if (str_starts_with($u, '/')) {
            return 'https://www.bitchute.com'.$u;
        }

        return $u;
    }
}
