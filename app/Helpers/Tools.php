<?php

namespace App\Helpers;

use App\Enums\Kind;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\Log;

class Tools
{
    public static function convertRedirectPathToUrl(string $path = ''): string
    {
        return config('app.url') . '/' . $path;
    }

    public static function convertYouTubeDurationToSeconds($youtube_time): int
    {
        $youtube_converted_time = new DateTime('@0'); // Unix epoch
        $current_time = new DateTime('@0'); // Unix epoch
        $youtube_converted_time->add(new DateInterval($youtube_time));

        $youtube_converted_time = $youtube_converted_time->format('Y-m-d H:i:s');
        $current_time = $current_time->format('Y-m-d H:i:s');
        $seconds = strtotime($youtube_converted_time) - strtotime($current_time);
        return $seconds;
    }

    // convert colon separated time to seconds
    public static function convertColonSeparatedTimeToSeconds($time): int
    {
        $time = explode(':', $time);
        $seconds = 0;
        $time = array_reverse($time);
        foreach ($time as $key => $value) {
            $seconds += $value * (60 ** $key);
        }
        return $seconds;
    }

    public static function validateDTOs(array $results): array
    {
        $validatedResults = [];
        foreach ($results as $result) {
            // Check if it's a valid ResultDTO with kind and platform
            if (!($result instanceof ResultDTO)) {
                Log::error("Invalid ResultDTO object detected.");
                continue;
            }

            // Validate Content (Video/Stream)
            $content = $result->content ?? null;
            if (($result->kind === Kind::Video || $result->kind === Kind::Stream)) {
                if ($result->content instanceof ContentDTO) {
                    $errors = [];

                    // Required fields for ContentDTO
                    if (empty($content->id)) $errors[] = "Content ID is missing.";
                    if (empty($content->name)) $errors[] = "Content name is missing.";
                    if (empty($content->creator_id)) $errors[] = "Creator ID is missing for content (Video).";
                    if (empty($content->publish_time)) $errors[] = "Publish time is missing.";
                    if ($content->thumbnail_url === null) $errors[] = "Thumbnail URL is missing.";

                    if (!empty($errors)) {
                        Log::error("Content validation failed for ContentDTO ID: {$content->id}", $errors);
                        continue;
                    }
                } else {
                    Log::error("ResultDTO is missing a valid ContentDTO.");
                    continue;
                }
            }

            // Validate Creator
            $creator = $result->creator ?? null;
            if ($creator instanceof CreatorDTO) {
                $errors = [];

                // Required fields for CreatorDTO
                if (empty($creator->id)) $errors[] = "Creator ID is missing.";
                if (empty($creator->name)) $errors[] = "Creator name is missing.";

                if (!empty($errors)) {
                    Log::error("Creator validation failed for CreatorDTO ID: {$creator->id}", $errors);
                    continue;
                }
            } else {
                Log::error("ResultDTO ID: {$content->id} is missing a valid CreatorDTO.");
                continue;
            }

            // If everything validates, add it to the validated results
            $validatedResults[] = $result;
        }

        return $validatedResults;
    }

}
