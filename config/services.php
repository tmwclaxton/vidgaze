<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'apify' => [
        'token' => env('APIFY_TOKEN'),
        'youtube_actor' => env('APIFY_YOUTUBE_ACTOR', 'streamers~youtube-scraper'),
        'podcast_actor' => env('APIFY_PODCAST_ACTOR', 'automation-lab~podcast-scraper'),
        'twitter_trends_actor' => env('APIFY_TWITTER_TRENDS_ACTOR', 'karamelo~twitter-trends-scraper'),
        'twitter_trends_timeout' => (int) env('APIFY_TWITTER_TRENDS_TIMEOUT', 240),
        /** Default input for karamelo~twitter-trends-scraper (country code, time windows). */
        'twitter_trends_input' => [
            'country' => env('APIFY_TWITTER_TRENDS_COUNTRY', '2'),
            'live' => filter_var(env('APIFY_TWITTER_TRENDS_LIVE', true), FILTER_VALIDATE_BOOLEAN),
            'hour1' => filter_var(env('APIFY_TWITTER_TRENDS_HOUR1', false), FILTER_VALIDATE_BOOLEAN),
            'hour3' => filter_var(env('APIFY_TWITTER_TRENDS_HOUR3', false), FILTER_VALIDATE_BOOLEAN),
            'hour6' => filter_var(env('APIFY_TWITTER_TRENDS_HOUR6', false), FILTER_VALIDATE_BOOLEAN),
            'hour12' => filter_var(env('APIFY_TWITTER_TRENDS_HOUR12', false), FILTER_VALIDATE_BOOLEAN),
            'hour24' => filter_var(env('APIFY_TWITTER_TRENDS_HOUR24', false), FILTER_VALIDATE_BOOLEAN),
            'day2' => filter_var(env('APIFY_TWITTER_TRENDS_DAY2', false), FILTER_VALIDATE_BOOLEAN),
            'day3' => filter_var(env('APIFY_TWITTER_TRENDS_DAY3', false), FILTER_VALIDATE_BOOLEAN),
        ],
    ],

    'firecrawl' => [
        'api_key' => env('FIRECRAWLER_API_KEY', env('FIRECRAWL_API_KEY')),
    ],

    /** LBRY JSON-RPC proxy used to resolve Odysee channel claims (avatars, metadata). */
    'odysee' => [
        'proxy_url' => env('ODYSEE_PROXY_URL', 'https://api.na-backend.odysee.com/api/v1/proxy'),
    ],

    'nanogpt' => [
        'key' => env('NANOGPT_API_KEY'),
        'search_ranking_enabled' => env('SEARCH_AI_RANKING_ENABLED', true),
        'search_ranking_model' => env('SEARCH_AI_RANKING_MODEL', 'gemini-2.0-flash-lite'),
    ],

];
