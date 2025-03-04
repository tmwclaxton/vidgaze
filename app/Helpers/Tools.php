<?php

namespace App\Helpers;

use DateInterval;
use DateTime;

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

}
