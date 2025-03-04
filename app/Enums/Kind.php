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
    case Chatroom = 'chatroom';


    public static function fromValue(string | Kind $value) : Kind
    {
     if(!is_string($value)){
         return $value;
     }
        switch($value) {
            case 'video':
                return self::Video;
            case 'creator':
                return self::Creator;
            case 'playlist':
                return self::Playlist;
            case 'stream':
                return self::Stream;
            case 'category':
                return self::Category;
            case 'rss':
                return self::RSS;
            case 'podcast':
                return self::Podcast;
            case 'podcast_episode':
                return self::PodcastEpisode;
            case 'album':
                return self::Album;
            case 'track':
                return self::Track;
            case 'chatroom':
                return self::Chatroom;
            default:
                throw new InvalidArgumentException('Invalid value for Kind');
        }
    }

    public function jsonSerialize(): string {
        return serialize($this);
    }
}


