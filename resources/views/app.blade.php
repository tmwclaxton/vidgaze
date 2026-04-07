<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
        <meta name="description" content="{{ config('seo.default_description') }}">
        <meta name="theme-color" content="#101828">
        <meta name="color-scheme" content="dark light">

        <title inertia>{{ config('app.name', 'VidGaze') }}</title>

        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => config('app.name', 'VidGaze'),
                'url' => rtrim((string) config('app.url'), '/').'/',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => rtrim((string) config('app.url'), '/').'/search?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>

        <!-- Fonts (self-hosted, /public/fonts/satoshi) -->
        <link rel="stylesheet" href="{{ asset('fonts/satoshi/satoshi.css') }}" />

        {{--favicon--}}
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">




        <x-pwa-assets />
        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body style="margin-bottom: 0px" class="font-sans antialiased dark:bg-vidgaze-blue h-full min-h-screen w-screen overflow-x-hidden text dark:textDark relative">

        @inertia
    </body>
</html>
