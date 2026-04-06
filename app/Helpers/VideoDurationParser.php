<?php

namespace App\Helpers;

/**
 * Normalizes video length from heterogeneous provider/scraper fields (Apify, APIs, Firecrawl markdown).
 *
 * Avoids common pitfalls: durationMs=0 masking a human-readable duration string, milliseconds vs seconds, etc.
 */
class VideoDurationParser
{
    /**
     * @param  array<string, mixed>  $item
     */
    public static function secondsFromScraperRow(array $item, int $depth = 0): int
    {
        if ($depth > 4) {
            return 0;
        }

        foreach (['videoDetails', 'contentDetails', 'details', 'snippet'] as $nest) {
            if (isset($item[$nest]) && is_array($item[$nest])) {
                $sec = self::secondsFromScraperRow($item[$nest], $depth + 1);
                if ($sec > 0) {
                    return $sec;
                }
            }
        }

        $stringKeys = [
            'duration', 'lengthText', 'videoLength', 'length', 'runtime',
            'durationFormatted', 'video_duration', 'videoDuration',
        ];
        foreach ($stringKeys as $key) {
            if (! array_key_exists($key, $item)) {
                continue;
            }
            $v = $item[$key];
            if (! is_string($v)) {
                continue;
            }
            $trim = trim($v);
            if ($trim === '') {
                continue;
            }
            if (preg_match('/^(live|premiere|upcoming|processing)\b/i', $trim)) {
                continue;
            }
            $sec = self::secondsFromMixed($v);
            if ($sec !== null && $sec > 0) {
                return $sec;
            }
        }

        $msKeys = ['durationMs', 'lengthMilliseconds', 'videoDurationMs', 'duration_ms'];
        foreach ($msKeys as $key) {
            if (! array_key_exists($key, $item)) {
                continue;
            }
            $v = $item[$key];
            if ($v === null || $v === '') {
                continue;
            }
            if (is_numeric($v) && (float) $v > 0) {
                return (int) round((float) $v / 1000);
            }
        }

        $secKeys = ['lengthSeconds', 'length_seconds', 'durationSeconds', 'duration_sec', 'seconds'];
        foreach ($secKeys as $key) {
            if (! array_key_exists($key, $item)) {
                continue;
            }
            $v = $item[$key];
            if ($v === null || $v === '') {
                continue;
            }
            if (is_numeric($v) && (float) $v > 0) {
                return (int) round((float) $v);
            }
        }

        if (isset($item['duration']) && is_numeric($item['duration']) && ! is_string($item['duration'])) {
            $n = (float) $item['duration'];
            if ($n > 0) {
                return self::secondsFromAmbiguousNumeric($n);
            }
        }

        return 0;
    }

    /**
     * Single field from an API (int seconds, ms blob, or display string).
     */
    public static function secondsFromMixed(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $t = trim($value);
            if ($t === '') {
                return null;
            }
            if (preg_match('/^(live|premiere|upcoming)\b/i', $t)) {
                return null;
            }
            if (ctype_digit($t)) {
                $n = (int) $t;

                return $n > 0 ? $n : null;
            }

            return self::secondsFromDisplayString($t);
        }
        if (is_int($value) || is_float($value)) {
            $n = (float) $value;
            if ($n <= 0) {
                return null;
            }

            return self::secondsFromAmbiguousNumeric($n);
        }

        return null;
    }

    public static function secondsFromDisplayString(string $raw): ?int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?$/i', $raw, $m)) {
            $h = (int) ($m[1] ?? 0);
            $min = (int) ($m[2] ?? 0);
            $s = (float) ($m[3] ?? 0);

            return (int) ($h * 3600 + $min * 60 + $s);
        }

        if (preg_match('/^(\d+)\s*(?:s|sec|secs|second|seconds)\b/i', $raw, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/(\d+):(\d{2}):(\d{2})/', $raw, $m)) {
            return (int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3];
        }

        if (preg_match('/(?<!\d)(\d{1,3}):(\d{2})(?!\d)/', $raw, $m)) {
            return (int) $m[1] * 60 + (int) $m[2];
        }

        if (ctype_digit($raw)) {
            $n = (int) $raw;

            return $n > 0 ? $n : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function millisecondsFromScraperRow(array $item): int
    {
        return self::secondsFromScraperRow($item) * 1000;
    }

    private static function secondsFromAmbiguousNumeric(float $n): int
    {
        // Above ~72h as *seconds* is implausible; treat as milliseconds (mislabeled totals).
        if ($n > 72 * 3600) {
            return (int) round($n / 1000);
        }

        return (int) round($n);
    }
}
