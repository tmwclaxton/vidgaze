<?php

//Convert seconds to hours minutes and seconds

/**
 * @param string $time works from s to h:m:s
 * @return float|int
 */
function convertTimeToSeconds(string $time)
{
    $seconds = 0;
    $iteration = 0;
    $sections = explode(':', $time);
    while (count($sections)) {
        $seconds += array_pop($sections) * pow(60, $iteration);
        $iteration++;
    }
    return $seconds;
}

//Front for front end display
function convertDuration($video_duration)
{
//    if (is_numeric($video_duration)) {
//        // Dailymotion's duration in seconds eg '845'
//        $interval = CarbonInterval::seconds($video_duration)->cascade();
//    } else {
//        // YouTube's duration in ISO8601 eg 'PT3M26S0'
//        $interval = CarbonInterval::make($video_duration);
//    }

    //01:01:01 shows up as 1:1:1 with this code
    //    $converted_duration = $interval->totalHours < 1 ? $interval->format('%i:%S') : $interval->format('%H:%i:%S');
    $video_duration = intval($video_duration);
    if ($video_duration < 3600) {
        $video_duration = gmdate("i:s", $video_duration);
    } else {
        $video_duration = gmdate("H:i:s", $video_duration);
    }

    //remove leading 0 for videos over an hour
    $converted_duration = ltrim($video_duration, '0');
    //add 0 back if that exposed a colan at beginning of duration
    if ((substr($converted_duration, 0, 1)) == ":") {
        $converted_duration = "0" . $converted_duration;
    }
    return $converted_duration;
}

//convert youtube duration to seconds
//taken from old site seems to work
function convertYouTubeDurationToSeconds($youtube_time)
{
    $youtube_converted_time = new DateTime('@0'); // Unix epoch
    $current_time = new DateTime('@0'); // Unix epoch
    $youtube_converted_time->add(new DateInterval($youtube_time));

    $youtube_converted_time = $youtube_converted_time->format('Y-m-d H:i:s');
    $current_time = $current_time->format('Y-m-d H:i:s');
    $seconds = strtotime($youtube_converted_time) - strtotime($current_time);
    return $seconds;
}


// Converts a number into a short version, eg: 1000 -> 1k
function number_format_short($n, $precision = 1)
{
    if ($n < 900) {
        // 0 - 900
        $n_format = number_format($n, $precision);
        $suffix = '';
    } elseif ($n < 900000) {
        // 0.9k-850k
        $n_format = number_format($n / 1000, $precision);
        $suffix = 'K';
    } elseif ($n < 900000000) {
        // 0.9m-850m
        $n_format = number_format($n / 1000000, $precision);
        $suffix = 'M';
    } elseif ($n < 900000000000) {
        // 0.9b-850b
        $n_format = number_format($n / 1000000000, $precision);
        $suffix = 'B';
    } else {
        // 0.9t+
        $n_format = number_format($n / 1000000000000, $precision);
        $suffix = 'T';
    }

    // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ($precision > 0) {
        $dotzero = '.' . str_repeat('0', $precision);
        $n_format = str_replace($dotzero, '', $n_format);
    }

    return $n_format . $suffix;
}

function capitalisePlatformName($platform)
{
    if ($platform === 'youtube') {
        $platform = ucfirst($platform);

        return str_replace('tube', 'Tube', $platform);
    }
    if ($platform === 'bitchute') {
        return 'BitChute';
    }

    return ucfirst($platform);
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getHttpType()
{
    return "http";
}

function convertRedirectPathToUrl(string $path = ''): string
{
    return config('app.url') . '/' . $path;
}

function getApiEndpoint(string $path = ''): string
{
    return getHttpType() . '://' . getServerName() . ":8002/api/" . $path;
}

function getServerName()
{
    if (!is_numeric($_SERVER['SERVER_NAME'][0])) {
        return $_SERVER['HTTP_HOST'];
    }
    return $_SERVER['SERVER_NAME'];
}

function convertNameToSlug(string $string): string
{
    return strtolower(str_replace([' ', '?', ':', '%', '&'], ['_', '', '', '', 'and'], $string));
}

