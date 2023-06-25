<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for the platforms VidGaze supports
    |
    */
    'google' => [
        'developer_key'=> env('GOOGLE_DEVELOPER_KEY'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_url' => [
            'link' => env('GOOGLE_REDIRECT_URL_LINK'),
            'import' => env('GOOGLE_REDIRECT_URL_IMPORT'),
        ],
    ],
    'twitch' => [
        'client_id' => env('TWITCH_CLIENT_ID'),
        'client_secret' => env('TWITCH_CLIENT_SECRET'),
        'redirect_url' => env('TWITCH_REDIRECT_URL'),
    ],
    'dailymotion' => [
        'client_key' => env('DAILYMOTION_CLIENT_KEY'),
        'client_secret' => env('DAILYMOTION_CLIENT_SECRET'),
        'redirect_url' => env('DAILYMOTION_REDIRECT_URL'),
    ],
    'vimeo' => [
        'developer_key'=> env('VIMEO_DEVELOPER_KEY'),
        'client_id' => env('VIMEO_CLIENT_ID'),
        'client_secret' => env('VIMEO_CLIENT_SECRET'),
        'redirect_url' => env('VIMEO_REDIRECT_URL'),
    ],
    'spotify' => [
        'client_id' => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
        'redirect_url' => env('SPOTIFY_REDIRECT_URL'),
    ],
];
