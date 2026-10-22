<?php

namespace App\Helpers\Scraping;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Parses Rumble pages fetched through crawl4ai into plain arrays that use the
 * same shape vidgaze already maps into DTOs.
 *
 * Rumble's embed id (used by the player) is "v" followed by the numeric video
 * id in base 36, so it can be built from the listing markup without opening
 * each video.
 */
class RumbleParser
{
    public static function embedId(string|int $numericId): ?string
    {
        $numericId = (string) $numericId;
        return ctype_digit($numericId) && $numericId !== '' ? 'v' . base_convert($numericId, 10, 36) : null;
    }

    private static function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        return new DOMXPath($dom);
    }

    private static function cls(string $class): string
    {
        return "contains(concat(' ', normalize-space(@class), ' '), ' $class ')";
    }

    private static function first(DOMXPath $xp, string $query, ?\DOMNode $ctx = null): ?DOMElement
    {
        $n = $xp->query($query, $ctx);
        return $n && $n->length ? $n->item(0) : null;
    }

    private static function text(?\DOMNode $n): ?string
    {
        if (!$n) return null;
        $t = trim(preg_replace('/\s+/u', ' ', $n->textContent));
        return $t === '' ? null : $t;
    }

    /** Slug from https://rumble.com/c/Slug?x or /user/Slug */
    public static function slugFromUrl(?string $url): ?string
    {
        if (!$url) return null;
        return preg_match('~/(?:c|user)/([^/?#]+)~', $url, $m) ? $m[1] : null;
    }

    /**
     * Channel header: name, avatar, banner, description.
     */
    public static function parseChannel(string $html): ?array
    {
        $xp = self::xpath($html);
        $name = self::text(self::first($xp, '//div[' . self::cls('channel-header--title') . ']//h1'));
        if (!$name) {
            return null;
        }
        $descNode = $xp->query('//meta[@name="description"]/@content');
        return [
            'name' => $name,
            'avatar' => self::first($xp, '//img[' . self::cls('channel-header--img') . ']')?->getAttribute('src') ?: null,
            'banner' => self::first($xp, '//img[' . self::cls('channel-header--backsplash-img') . ']')?->getAttribute('src') ?: null,
            'description' => $descNode && $descNode->length ? trim($descNode->item(0)->nodeValue) : '',
        ];
    }

    /**
     * Video grid on https://rumble.com/c/{slug}/videos.
     */
    public static function parseChannelVideos(string $html, int $limit = 30): array
    {
        $xp = self::xpath($html);
        $videos = [];
        foreach ($xp->query('//rum-video-thumbnail[@video-id]') as $el) {
            /** @var DOMElement $el */
            $embed = self::embedId($el->getAttribute('video-id'));
            $title = trim($el->getAttribute('video-title'));
            if (!$embed || $title === '' || isset($videos[$embed])) continue;
            $time = $el->getAttribute('time') ?: $el->getAttribute('scheduled-time');
            $videos[$embed] = [
                'id' => $embed,
                'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5),
                'duration' => (int) $el->getAttribute('duration'),
                'upload_date' => $time ?: null,
                'thumb' => $el->getAttribute('src') ?: null,
                'url' => $el->getAttribute('url') ?: null,
                'channel_name' => $el->getAttribute('name') ?: null,
                'channel_avatar' => $el->getAttribute('avatar') ?: null,
                'channel_slug' => self::slugFromUrl($el->getAttribute('creator-url')),
            ];
            if (count($videos) >= $limit) break;
        }
        return array_values($videos);
    }

    /**
     * Search page https://rumble.com/search/all?q=...
     * Returns ['videos' => [...], 'channels' => [...]].
     */
    public static function parseSearch(string $html, int $videoLimit = 20, int $channelLimit = 3): array
    {
        $xp = self::xpath($html);
        $videos = [];
        foreach ($xp->query('//article[' . self::cls('video-item') . ']') as $art) {
            $numeric = self::first($xp, './/*[@data-id][' . self::cls('rumbles-vote') . ']', $art)?->getAttribute('data-id');
            $embed = $numeric ? self::embedId($numeric) : null;
            $title = self::text(self::first($xp, './/h3[' . self::cls('video-item--title') . ']', $art));
            $by = self::first($xp, './/a[' . self::cls('video-item--by-a') . ']', $art);
            $slug = self::slugFromUrl($by?->getAttribute('href'));
            if (!$embed || !$title || !$slug || isset($videos[$embed])) continue;
            $dur = self::first($xp, './/*[' . self::cls('video-item--duration') . ']', $art)?->getAttribute('data-value');
            $videos[$embed] = [
                'id' => $embed,
                'title' => $title,
                'duration' => $dur ? self::colonToSeconds($dur) : 0,
                'upload_date' => self::first($xp, './/time[@datetime]', $art)?->getAttribute('datetime'),
                'thumb' => self::first($xp, './/img[' . self::cls('video-item--img') . ']', $art)?->getAttribute('src'),
                'channel_name' => preg_replace('/\s*Verified$/', '', self::text(self::first($xp, './/div[' . self::cls('ellipsis-1') . ']', $by)) ?? self::text($by) ?? $slug),
                'channel_slug' => $slug,
            ];
            if (count($videos) >= $videoLimit) break;
        }

        $channels = [];
        foreach ($xp->query('//a[starts-with(@href, "/c/")][' . self::cls('inset-0') . ']') as $a) {
            $slug = self::slugFromUrl($a->getAttribute('href'));
            if (!$slug || isset($channels[$slug])) continue;
            $name = self::text(self::first($xp, '..//h3//span', $a));
            if (!$name) continue;
            $channels[$slug] = ['slug' => $slug, 'name' => $name];
            if (count($channels) >= $channelLimit) break;
        }

        return ['videos' => array_values($videos), 'channels' => array_values($channels)];
    }

    public static function colonToSeconds(string $time): int
    {
        $seconds = 0;
        foreach (array_reverse(explode(':', trim($time))) as $i => $part) {
            $seconds += ((int) $part) * (60 ** $i);
        }
        return $seconds;
    }
}
