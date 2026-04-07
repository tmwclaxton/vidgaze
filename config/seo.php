<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default meta description
    |--------------------------------------------------------------------------
    |
    | Used when a page does not pass a specific description. Override with
    | SEO_DEFAULT_DESCRIPTION in your environment.
    |
    */

    'default_description' => env(
        'SEO_DEFAULT_DESCRIPTION',
        'Discover and watch videos, live streams, podcasts, and music from multiple platforms—all in one place on VidGaze.'
    ),

    /*
    |--------------------------------------------------------------------------
    | Default Open Graph / social preview image
    |--------------------------------------------------------------------------
    |
    | Absolute URL, or a site-relative path (e.g. /images/share.png). Relative
    | paths are resolved against APP_URL.
    |
    */

    'default_og_image' => env('SEO_DEFAULT_OG_IMAGE', '/favicon.ico'),

    /*
    |--------------------------------------------------------------------------
    | Twitter / X handle for twitter:site (include the @ prefix)
    |--------------------------------------------------------------------------
    */

    'twitter_site' => env('SEO_TWITTER_SITE'),

];
