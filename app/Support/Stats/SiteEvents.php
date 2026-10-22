<?php

namespace App\Support\Stats;

use App\Models\SiteEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

/**
 * Privacy safe activity tracking. Stores only the event type, the route name,
 * a video id or a cleaned search term. Never an IP, user, session or user agent.
 * Failures are swallowed so tracking can never break a page.
 */
class SiteEvents
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|preview|facebookexternalhit|headless|lighthouse|curl|wget|python|monitor|uptime/i';

    public static function isBot(?Request $request): bool
    {
        $ua = (string) ($request?->userAgent() ?? '');
        return $ua === '' || preg_match(self::BOT_PATTERN, $ua) === 1;
    }

    public static function record(string $type, ?string $page = null, ?int $videoId = null, ?string $term = null, ?Request $request = null): void
    {
        if (self::isBot($request ?? request())) {
            return;
        }
        try {
            SiteEvent::create([
                'type' => $type,
                'page' => $page ? Str::limit($page, 48, '') : null,
                'video_id' => $videoId,
                'term' => $term,
            ]);
        } catch (Throwable) {
            // tracking is best effort
        }
    }

    /**
     * Lower case, trimmed and shortened search term, or null when it might be
     * personal: email addresses, links, handles, long numbers or long phrases.
     */
    public static function cleanTerm(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $term = Str::lower(trim(preg_replace('/\s+/u', ' ', $raw)));
        if ($term === '' || mb_strlen($term) < 2 || mb_strlen($term) > 60) {
            return null;
        }
        if (preg_match('/[@#]|https?:|www\.|\.(com|net|org|uk|tv|io)\b|\d{4,}|\d[\d\s\-().+]{6,}\d/u', $term)) {
            return null;
        }
        if (count(explode(' ', $term)) > 6) {
            return null;
        }
        return $term;
    }
}
