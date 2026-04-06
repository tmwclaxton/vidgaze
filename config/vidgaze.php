<?php

return [

    'categorisation_min_confidence' => (float) env('VIDGAZE_CATEGORY_MIN_CONFIDENCE', 0.55),

    'trend_picks' => [
        'max_cached_video_ids' => (int) env('VIDGAZE_TREND_PICK_MAX_IDS', 48),
        'ttl_seconds' => (int) env('VIDGAZE_TREND_PICK_TTL', 172800),
        'max_trends_per_run' => (int) env('VIDGAZE_TWITTER_TRENDS_MAX_PER_RUN', 8),
        'max_videos_per_trend' => (int) env('VIDGAZE_TWITTER_TREND_VIDEOS_EACH', 3),
        /** Stored per topic for home trend view (can be larger than merge into VidGaze picks). */
        'max_videos_per_trend_feed' => (int) env('VIDGAZE_TWITTER_TREND_FEED_VIDEOS_EACH', 24),
        'ai_filter_trends' => filter_var(env('VIDGAZE_TWITTER_TRENDS_AI_FILTER', true), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    | AI-generated search snippets per category (see app:fetch-category-discovery-feed).
    */
    'category_discovery' => [
        'slugs' => array_values(array_filter(array_map('trim', explode(',', (string) env('VIDGAZE_CATEGORY_DISCOVERY_SLUGS', 'music,gaming,sports'))))),
        'max_videos_per_category' => (int) env('VIDGAZE_CATEGORY_DISCOVERY_MAX_VIDEOS', 24),
        'ttl_seconds' => (int) env('VIDGAZE_CATEGORY_DISCOVERY_TTL', 172800),
        'brand_decay_factor' => (float) env('VIDGAZE_CATEGORY_DISCOVERY_BRAND_DECAY', 0.92),
        'initial_brand_score' => (float) env('VIDGAZE_CATEGORY_DISCOVERY_BRAND_INITIAL', 100),
        'watch_boost_amount' => (float) env('VIDGAZE_CATEGORY_DISCOVERY_WATCH_BOOST', 8),
        /** Minimum reported watch seconds in a single view update to apply boost (once per session via view listener). */
        'watch_boost_min_seconds' => (int) env('VIDGAZE_CATEGORY_DISCOVERY_WATCH_BOOST_MIN_S', 45),
        'recent_video_window' => (int) env('VIDGAZE_CATEGORY_DISCOVERY_RECENT_WINDOW', 40),
        'recent_ttl_seconds' => (int) env('VIDGAZE_CATEGORY_DISCOVERY_RECENT_TTL', 604800),
        'api_max_videos' => (int) env('VIDGAZE_CATEGORY_DISCOVERY_API_MAX', 24),
    ],

];
