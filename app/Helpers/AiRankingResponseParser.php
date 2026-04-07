<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class AiRankingResponseParser
{
    /**
     * @param  list<int>  $allowedIds
     * @return list<int>|null
     */
    public static function parseIdArrayFromModelContent(string $content, array $allowedIds): ?array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/', '', $content) ?? $content;

        $allowedSet = array_fill_keys($allowedIds, true);

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            if (preg_match('/\[\s*[\d\s,]+\s*\]/', $content, $m)) {
                $decoded = json_decode($m[0], true);
            }
        }

        if (! is_array($decoded)) {
            Log::warning('AiRankingResponseParser: could not parse JSON array from model', ['snippet' => substr($content, 0, 200)]);

            return null;
        }

        $out = [];
        foreach ($decoded as $item) {
            if (is_int($item) || (is_string($item) && ctype_digit($item))) {
                $id = (int) $item;
                if (isset($allowedSet[$id]) && ! in_array($id, $out, true)) {
                    $out[] = $id;
                }
            }
        }

        foreach ($allowedIds as $id) {
            if (! in_array($id, $out, true)) {
                $out[] = $id;
            }
        }

        if (count($out) !== count($allowedIds)) {
            return null;
        }

        return $out;
    }

    public static function squish(string $s): string
    {
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;

        return trim(mb_substr($s, 0, 200));
    }
}
