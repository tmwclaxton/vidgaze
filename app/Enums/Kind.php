<?php

namespace App\Enums;

use InvalidArgumentException;

enum Kind : string
{
    case Video = 'video';
    case Creator = 'creator';
    case Playlist = 'playlist';
    case Stream = 'stream';
    case Category = 'category';
    case RSS = 'rss';
    case Podcast = 'podcast';
    case PodcastEpisode = 'podcast_episode';
    case Album = 'album';
    case Track = 'track';


    public static function fromValue(string | Kind $value) : Kind
    {
     if(!is_string($value)){
         return $value;
     }
        return match ($value) {
            'video' => self::Video,
            'creator' => self::Creator,
            'playlist' => self::Playlist,
            'stream' => self::Stream,
            'category' => self::Category,
            'rss' => self::RSS,
            'podcast' => self::Podcast,
            'podcast_episode' => self::PodcastEpisode,
            'album' => self::Album,
            'track' => self::Track,
            default => throw new InvalidArgumentException('Invalid value for Kind'),
        };
    }

    public function jsonSerialize(): string {
        return serialize($this);
    }
}


