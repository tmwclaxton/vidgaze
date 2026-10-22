<?php

namespace App\Services\Recommendations;

/**
 * Pure ranking helpers for home rows and watch page suggestions.
 *
 * Works on plain arrays so it can be unit tested without a database.
 * A candidate is an array with: id, creator_id, platform, title, description,
 * duration (seconds), published (unix time or null), pinned (bool),
 * views, likes, impressions, tags (array of strings).
 */
class VideoRanker
{
    /** Half life of the freshness score, in days. */
    public const DEFAULT_HALF_LIFE_DAYS = 10.0;

    private const STOPWORDS = [
        'the', 'a', 'an', 'and', 'or', 'of', 'to', 'in', 'on', 'for', 'with', 'is', 'are', 'was', 'be',
        'this', 'that', 'it', 'its', 'at', 'by', 'from', 'as', 'your', 'you', 'my', 'me', 'we', 'our',
        'i', 'he', 'she', 'they', 'his', 'her', 'their', 'what', 'how', 'why', 'who', 'when', 'will',
        'not', 'no', 'all', 'new', 'video', 'official', 'live', 'full', 'episode', 'part', 'vs',
        'de', 'la', 'el', 'le', 'les', 'und', 'der', 'die', 'das', 'amp', 'http', 'https', 'www', 'com',
        '2024', '2025', '2026', '2027',
    ];

    /**
     * Freshness in [0, 1]: 1 when just published, 0.5 after one half life.
     * Unknown publish time counts as fairly old.
     */
    public static function freshness(?int $published, int $now, float $halfLifeDays = self::DEFAULT_HALF_LIFE_DAYS): float
    {
        if (!$published) {
            return 0.15;
        }
        $ageDays = max(0, ($now - $published) / 86400);
        return pow(0.5, $ageDays / max(0.5, $halfLifeDays));
    }

    /**
     * Quality in roughly [0.2, 1.1] from cheap metadata: duration, description,
     * shouty or emoji heavy titles.
     */
    public static function quality(array $v): float
    {
        $q = 1.0;

        $duration = (int) ($v['duration'] ?? 0);
        if ($duration === 0) {
            $q *= 0.85;            // unknown (often a live stream or a scrape gap)
        } elseif ($duration < 60) {
            $q *= 0.35;            // shorts belong in the shorts row
        } elseif ($duration < 120) {
            $q *= 0.8;
        } elseif ($duration > 4 * 3600) {
            $q *= 0.75;            // 24/7 loops and endless streams
        }

        $desc = trim((string) ($v['description'] ?? ''));
        if (mb_strlen($desc) < 20) {
            $q *= 0.85;
        } elseif (mb_strlen($desc) > 200) {
            $q *= 1.05;
        }

        $title = (string) ($v['title'] ?? '');
        $letters = preg_replace('/[^\p{L}]/u', '', $title);
        $len = mb_strlen($letters);
        if ($len >= 12) {
            $upper = mb_strlen(preg_replace('/[^\p{Lu}]/u', '', $letters));
            if ($upper / $len > 0.6) {
                $q *= 0.75;        // ALL CAPS clickbait
            }
        }
        $emoji = preg_match_all('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $title);
        if ($emoji >= 3) {
            $q *= 0.85;
        }
        if (preg_match_all('/[!?]/', $title) >= 3) {
            $q *= 0.9;
        }
        if (mb_strlen($title) < 8) {
            $q *= 0.8;
        }

        return $q;
    }

    /** Popularity multiplier from on site signals, >= 1. */
    public static function popularity(array $v): float
    {
        $signal = (int) ($v['views'] ?? 0) + 3 * (int) ($v['likes'] ?? 0) + 0.05 * (int) ($v['impressions'] ?? 0);
        return 1 + 0.25 * log1p(max(0, $signal));
    }

    /** Deterministic jitter in [1 - $amount, 1 + $amount] so rows rotate per seed. */
    public static function jitter(int $id, string $seed, float $amount = 0.15): float
    {
        $h = crc32($seed . ':' . $id) / 4294967295;
        return 1 - $amount + 2 * $amount * $h;
    }

    /**
     * Overall score. $ctx may hold:
     *  half_life (days), seed (string), boost_creators (id => factor),
     *  seen (id => true, lowers already watched videos).
     */
    public static function score(array $v, int $now, array $ctx = []): float
    {
        $fresh = self::freshness($v['published'] ?? null, $now, $ctx['half_life'] ?? self::DEFAULT_HALF_LIFE_DAYS);
        // Blend: freshness matters most, but an older good video can still win.
        $s = (0.25 + 0.75 * $fresh) * self::quality($v) * self::popularity($v);

        if (!empty($v['pinned'])) {
            $s *= 1.2;
        }
        if (isset($ctx['boost_creators'][$v['creator_id'] ?? 0])) {
            $s *= $ctx['boost_creators'][$v['creator_id']];
        }
        if (isset($ctx['seen'][$v['id']])) {
            $s *= 0.3;
        }
        if (isset($ctx['seed'])) {
            $s *= self::jitter((int) $v['id'], (string) $ctx['seed']);
        }
        return $s;
    }

    /**
     * Pick $n items from candidates that already carry a 'score', best first,
     * with at most $maxPerCreator per channel and no platform taking more than
     * $maxPlatformShare of the row while another platform has candidates.
     * Ids in $exclude are skipped; near duplicate titles are skipped too.
     * Limits relax only when there are not enough candidates.
     *
     * @return array<int, array> the chosen candidates in order
     */
    public static function diversify(array $candidates, int $n, int $maxPerCreator = 1, float $maxPlatformShare = 0.67, array $exclude = []): array
    {
        usort($candidates, fn ($a, $b) => $b['score'] <=> $a['score']);
        $candidates = array_values(array_filter($candidates, fn ($c) => !isset($exclude[$c['id']])));

        $platforms = array_unique(array_map(fn ($c) => $c['platform'] ?? '', $candidates));
        $platformCap = count($platforms) > 1 ? max(1, (int) round($n * $maxPlatformShare)) : $n;

        // Fill strictly first, then top up with progressively looser rules so
        // the best diverse picks always come first.
        $picked = [];
        $passes = [
            [$maxPerCreator, $platformCap, true],
            [$maxPerCreator, $n, true],
            [$maxPerCreator + 1, $n, true],
            [PHP_INT_MAX, $n, false],
        ];
        foreach ($passes as [$creatorCap, $pCap, $dedupeTitles]) {
            $picked = self::greedy($candidates, $n, $creatorCap, $pCap, $dedupeTitles, $picked);
            if (count($picked) >= $n) {
                break;
            }
        }
        return $picked;
    }

    private static function greedy(array $candidates, int $n, int $creatorCap, int $platformCap, bool $dedupeTitles, array $picked = []): array
    {
        $perCreator = [];
        $perPlatform = [];
        $titles = [];
        $chosen = [];
        foreach ($picked as $p) {
            $perCreator[$p['creator_id'] ?? 0] = ($perCreator[$p['creator_id'] ?? 0] ?? 0) + 1;
            $perPlatform[$p['platform'] ?? ''] = ($perPlatform[$p['platform'] ?? ''] ?? 0) + 1;
            $titles[] = self::tokens($p['title'] ?? '');
            $chosen[$p['id']] = true;
        }
        foreach ($candidates as $c) {
            if (count($picked) >= $n) {
                break;
            }
            if (isset($chosen[$c['id']])) {
                continue;
            }
            $cr = $c['creator_id'] ?? 0;
            $pl = $c['platform'] ?? '';
            if (($perCreator[$cr] ?? 0) >= $creatorCap || ($perPlatform[$pl] ?? 0) >= $platformCap) {
                continue;
            }
            $tokens = self::tokens($c['title'] ?? '');
            if ($dedupeTitles) {
                foreach ($titles as $t) {
                    if (self::jaccard($tokens, $t) >= 0.7) {
                        continue 2;
                    }
                }
            }
            $titles[] = $tokens;
            $chosen[$c['id']] = true;
            $picked[] = $c;
            $perCreator[$cr] = ($perCreator[$cr] ?? 0) + 1;
            $perPlatform[$pl] = ($perPlatform[$pl] ?? 0) + 1;
        }
        return $picked;
    }

    /** Lowercase content words of a string, as a set (word => true). */
    public static function tokens(string $text): array
    {
        $text = mb_strtolower($text);
        preg_match_all('/[\p{L}\p{N}]{3,}/u', $text, $m);
        $out = [];
        foreach ($m[0] as $w) {
            if (!in_array($w, self::STOPWORDS, true)) {
                $out[$w] = true;
            }
        }
        return $out;
    }

    public static function jaccard(array $a, array $b): float
    {
        if (!$a || !$b) {
            return 0.0;
        }
        $inter = count(array_intersect_key($a, $b));
        return $inter / (count($a) + count($b) - $inter);
    }

    /**
     * Relatedness of $c to the video being watched, in roughly [0, 1.5].
     * Text overlap of titles and tags is the main signal; same channel and
     * same category add a bit; $fulltext is an optional normalised MySQL score.
     */
    public static function relatedness(array $current, array $c, float $fulltext = 0.0): float
    {
        $titleSim = self::jaccard(self::tokens($current['title'] ?? ''), self::tokens($c['title'] ?? ''));
        $tagSim = self::jaccard(
            self::tokens(implode(' ', $current['tags'] ?? [])),
            self::tokens(implode(' ', $c['tags'] ?? []))
        );
        $s = 0.45 * $titleSim + 0.25 * $tagSim + 0.35 * min(1.0, $fulltext);
        if (($current['category_id'] ?? null) !== null && ($current['category_id'] ?? null) === ($c['category_id'] ?? null)) {
            $s += 0.2;
        }
        if (($current['creator_id'] ?? null) === ($c['creator_id'] ?? null)) {
            $s += 0.25;
        }
        return $s;
    }
}
