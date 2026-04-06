# VidGaze (open-source, free speech)
## _The Ultimate Video-Streaming App_

VidGaze is a website and mobile-ready video-streaming app designed to empower content creators and make viewing content from multiple platforms a delightful experience. 

![VideGaze Welcome Page](/public/images/banners/VidGaze-Banner.png)

## Features

- Watch YouTube, Dailymotion, Vimeo, Twitch, Rumble and more (Odysee on the horizon) all in one place!
- Upload videos to any platform with a simple multi-uploader form (limited in scope)
- Cross-platform content managament (limited in scope)

## How to launch the VidGaze container:

1. Clone the repo
2. Run `sail build` (uses PHP 8.4; rebuild after upgrading from older images)
3. Run `sail up`
4. Run `sail artisan db:wipe`
5. Run `sail artisan migrate`
6. Run `sail artisan migrate --path=database/patches`

## Tech

VidGaze uses a number of open source projects to work properly:

- [Laravel] 13.x (requires PHP 8.4+ with current Symfony 8 deps) — A web application framework with expressive, elegant syntax.
- [InertiaJS] - Inertia.js is a lightweight JavaScript library that allows developers to build single-page applications (SPAs).
- [MYSQL] - a relational database management system (RDBMS) developed by Oracle that is based on structured query language (SQL).

## How to use?

* Visit https://www.vidgaze.tv
* Download VidGaze on the Apple App Store or the Google Play Store (Coming soon :P)

## Development

Want to contribute as a developer / moderator / promotor? Great!

Send us an email at vidgaze@gmail.com

