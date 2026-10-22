<?php

return [

    // Self hosted crawl4ai is the primary scraper for Rumble and YouTube.
    'crawl4ai' => [
        'enabled' => env('CRAWL4AI_ENABLED', true),
        'url' => env('CRAWL4AI_URL'),                 // e.g. http://crawl4ai:11235
        'token' => env('CRAWL4AI_API_TOKEN'),
        'timeout' => (int) env('CRAWL4AI_TIMEOUT', 90),
        'page_timeout_ms' => (int) env('CRAWL4AI_PAGE_TIMEOUT_MS', 30000),
        'cache_mode' => env('CRAWL4AI_CACHE_MODE', 'bypass'),
        'retries' => (int) env('CRAWL4AI_RETRIES', 2),
        // crawl4ai site_profile for YouTube channel pages (consent cookies). Empty disables.
        'youtube_profile' => env('CRAWL4AI_YOUTUBE_PROFILE', 'youtube'),
        'rumble_banner_batch' => (int) env('CRAWL4AI_RUMBLE_BANNER_BATCH', 20),
    ],

    // Apify is only a fallback when crawl4ai fails. Defaults are the cheapest
    // settings that work for each actor.
    'apify' => [
        'token' => env('APIFY_TOKEN'),
        'fallback_enabled' => env('APIFY_FALLBACK_ENABLED', true),
        'daily_cap' => (int) env('APIFY_DAILY_RUN_CAP', 20),
        'cache_minutes' => (int) env('APIFY_CACHE_MINUTES', 360),
        'memory_mb' => (int) env('APIFY_MEMORY_MB', 512),
        'timeout_secs' => (int) env('APIFY_TIMEOUT_SECS', 90),
        'max_charge_usd' => env('APIFY_MAX_CHARGE_USD', 0.05),
        'use_proxy' => env('APIFY_USE_PROXY', false),
        'proxy_groups' => env('APIFY_PROXY_GROUPS', ''),  // empty = datacenter when use_proxy is on
        'rumble' => [
            // pay per result: $0.001 per video + $0.005 start
            'actor' => env('APIFY_RUMBLE_ACTOR', 'memo23~rumble-scraper'),
            // returns channel avatar and banner, which memo23 does not: $0.0015 per channel + $0.005 start
            'creator_actor' => env('APIFY_RUMBLE_CREATOR_ACTOR', 'azzouzana~rumble-all-inclusive-scraper'),
            'search_max' => (int) env('APIFY_RUMBLE_SEARCH_MAX', 20),
            'channel_videos_max' => (int) env('APIFY_RUMBLE_CHANNEL_VIDEOS_MAX', 30),
            'creators_max' => (int) env('APIFY_RUMBLE_CREATORS_MAX', 10),
        ],
    ],
    'scraper' => ['key' => env('SCRAPER_KEY')],
    'nanogpt' => [
        'key' => env('NANOGPT_API_KEY'),
        // gemini-2.0-flash-001 was disabled by NanoGPT (400 "Model is disabled").
        // DeepSeek V4 Flash is the default for every NanoGPT call.
        'model' => env('NANOGPT_MODEL', 'deepseek/deepseek-v4-flash'),
        'categorise_model' => env('NANOGPT_CATEGORISE_MODEL', env('NANOGPT_MODEL', 'deepseek/deepseek-v4-flash')),
        'search_model' => env('NANOGPT_SEARCH_MODEL', env('NANOGPT_MODEL', 'deepseek/deepseek-v4-flash')),
        'categorise_batch' => (int) env('NANOGPT_CATEGORISE_BATCH', 50),
    ],


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

];
