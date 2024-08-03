# VidGaze (open-source, free speech)
## _The Ultimate Video-Streaming App by the People_

VidGaze is a website and mobile-ready video-streaming app designed to empower content creators and make viewing content from multiple platforms a delightful experience. 

## Mission

Five years ago, I launched VidGaze with a mission to champion free speech through a pragmatic lens.   While certain things are undeniably unacceptable, it’s clear a single arbiter of truth doesn’t work very well.  VidGaze was built to address this by aggregating content from multiple sources, outsourcing moderation to these 3rd party sites, offering a balanced perspective, preventing shadowbans, censorship and unfair demonetisation by allowing creators to change their primary content sources.

Despite these intentions, VidGaze ultimately failed. In hindsight, the root cause was my approach: I tried to build it as a capitalist in an increasingly post-capitalist world. The barriers in this industry are immense. Even after solving the creator-viewer chicken-and-egg problem, a problem alternatives like Rumble, PeerTube and plenty have failed at, these challenges proved insurmountable for me.

To compete in today’s digital landscape, you need a flawless mobile app and website that constantly gets updated, the kind of care only billion-dollar companies can deliver. You need a sophisticated recommendation algorithm, another domain dominated by tech giants. You need a massive marketing campaign to lure users away from established platforms, and yet, people have grown distrustful of social media.

Despite VidGaze’s early successes, it became clear that overcoming these hurdles would require resources beyond my reach. Continuing would have meant sacrificing my life to a fight I couldn’t win.
Around the same time, the capitalists began their attempts to tackle free speech. Notably, X.com (formerly Twitter) merely flipped the political bias. It was a disaster, especially with its olligarch owner banning users who hurt his feelings.  About a year ago, I made the difficult decision to shut down VidGaze. The platform lacked sufficient interest, and I couldn’t sustain the same level of effort. Investors weren’t willing to back it, either they didn’t believe it could generate returns, and given my approach, they were right. I wasn’t playing their game, and I couldn’t win it.

But now, I see a different path forward: open-sourcing and equity-as-a-service (explained further on the website).

When I was trying to operate as a capitalist, open-sourcing VidGaze wasn’t an option. Investors crave “moats” or barriers to competition. What if someone stole the idea? But now, I say: to hell with the capitalists. VidGaze will be a platform for the proletariat, the working people. If your primary tax is income tax, this is for you. If you don’t like how I’m hosting it, you’re free to host it yourself and maybe a federated model like Robosats would work well.  

The platforms you use every day are owned by capitalists. They censor your comments, suppress your thoughts, and control what you see. They’ve waged a class war, and they’re winning. Change can’t happen if we get silenced when we try to speak up. VidGaze can be different. It can be the people's.  

## How to launch the VidGaze container:

1. Clone the repo
2. Run `sail build`
3. Run `sail up`
4. Run `sail artisan db:wipe`
5. Run `sail artisan migrate`
6. Run `sail artisan migrate --path=database/patches`

## Features

- Watch YouTube, Dailymotion, Vimeo, Odysee (not added), Twitch, Rumble (not added) and more all in one app
- Upload videos to any platform with a simple multi-uploader form (limited in scope)
- Cross-platform content managament (limited in scope)

## Tech

VidGaze uses a number of open source projects to work properly:

- [Laravel] - A web application framework with expressive, elegant syntax.
- [InertiaJS] - Inertia.js is a lightweight JavaScript library that allows developers to build single-page applications (SPAs).
- [MYSQL] - a relational database management system (RDBMS) developed by Oracle that is based on structured query language (SQL).

 

## Usage

```sh
visit vidgaze.tv
download VidGaze on the Apple App Store or the Google Play Store
```

## Development

Want to contribute? Great!

Send us an email at vidgaze@gmail.com

