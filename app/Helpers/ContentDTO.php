<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Models\PodcastModels\Podcast;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamSource;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoSource;
use Carbon\Carbon;

class ContentDTO
{
    public ContentDTO $category;
    public array $podcast_episodes;

    public Kind | string $kind;
    public Platform | string $platform;

    public string $creator_id;
    public string $category_slug;
    public bool $assignable;
    public int $dislikes;
    public Carbon $publish_time;
    public Carbon $upload_time;
    public int $views;
    public int $likes;
    public string $thumbnail_url;
    public string | null $description;
    //public $bio;
    public string | null $region;
    public string | null $language;
    public array $tags;
    public string $name;
    public string $twitch_login;
    public string $duration;
//    public bool $explicit;
    public Audience $audience;

    //    Podcast Specific Variables
    public string $audio_url;
    public string $rss_url;
    public string $guid;
    public string $subcategory_name;
    public string $audio_file_type;
    public int $result_index;
    public string $id;
    public bool $is_live;


    public function __construct(Platform $platform, Kind $kind, string $id)
    {
        $this->platform = $platform;
        $this->kind = $kind;
        $this->id = $id;
    }

    public function save($creator_id)
    {
        return match ($this->kind) {
            Kind::Video => $this->saveVideo($creator_id),
            Kind::Stream => $this->saveStream($creator_id),
//            Kind::Podcast => $this->savePodcast($creator_id),
            default => throw new \Exception("Invalid Kind"),
        };
    }


    public function saveVideo($creator_id): Video
    {
        $var = VideoSource::where('source_name', '=', $this->platform->value)
            ->where('external_id', '=', $this->id)
            ->firstOr(function () use ($creator_id) {
                $video = Video::create([
                    'slug' => $this->platform->getPrefix().'_'.$this->id,
                    'title' => $this->name,
                    'description' => $this->description ?? null,
                    'thumbnail_url' => $this->thumbnail_url ?? null,
                    'duration' => $this->duration,
                    'time_uploaded' => $this->upload_time ?? null,
                    'time_published' => $this->publish_time ?? null,
                    'region' => $this->region ?? null,
                    'language' => $this->language ?? null,
                    'tags' => json_encode($this->tags ?? null) ,
                    'creator_id' => $creator_id,
                    'preferred_source' => $this->platform->value,
                    'audience' => $this->audience->value ?? Audience::ALL,
//                'category_id' => $this->category->save()->id,
                ]);
                VideoSource::create([
                    'source_name' => $this->platform->value,
                    'external_id' => $this->id,
                    'video_id' => $video->id
                ]);
                return $video;
            });
        return $var instanceof Video? $var : $var->video()->first();
    }

    public function saveStream($creator_id) : Stream
    {
        $var = StreamSource::where('source_name', '=', $this->platform->value)
            ->where('external_id', '=', $this->id)
            ->firstOr(function () use ($creator_id) {
                $stream = Stream::create([
                    'slug' => $this->platform->getPrefix().'_'.$this->id,
                    'title' => $this->name,
                    'description' => $this->description ?? null,
                    'thumbnail_url' => $this->thumbnail_url ?? null,
                    'started_at' => $this->publish_time ?? null,
                    'region' => $this->region ?? null,
                    'language' => $this->language ?? null,
                    'tags' => json_encode($this->tags ?? null) ,
                    'creator_id' => $creator_id,
                    'preferred_source' => $this->platform->value,
                    'audience' => $this->audience->value ?? Audience::ALL,
                    'is_live' => $this->is_live??null,
//                'category_id' => $this->category->save()->id,
                ]);
                StreamSource::create([
                    'source_name' => $this->platform->value,
                    'external_id' => $this->id,
                    'stream_id' => $stream->id
                ]);
                return $stream;
            });
        return $var instanceof Stream? $var : $var->stream()->first();
    }
//    public function savePodcast($creator_id): Podcast
//    {
//
//    }
    public static function convertFromStdClass($content)
    {
        $content_dto = new self(Platform::fromValue($content->platform), Kind::fromValue($content->kind), $content->id);
        $content_dto->name = $content->name;
        if(isset($content->thumbnail_url)) $content_dto->thumbnail_url = $content->thumbnail_url;
        if(isset($content->description)) $content_dto->description = $content->description;
        if(isset($content->duration)) $content_dto->duration = $content->duration;
        if(isset($content->upload_time)) $content_dto->upload_time = Carbon::parse($content->upload_time);
        if(isset($content->publish_time)) $content_dto->publish_time = Carbon::parse($content->publish_time);
        if(isset($content->region)) $content_dto->region = $content->region;
        if(isset($content->language)) $content_dto->language = $content->language;
        if(isset($content->tags)) $content_dto->tags = $content->tags;
        if(isset($content->views)) $content_dto->views = $content->views;
        if(isset($content->likes)) $content_dto->likes = $content->likes;
        if(isset($content->dislikes)) $content_dto->dislikes = $content->dislikes;
        if(isset($content->assignable)) $content_dto->assignable = $content->assignable;
        if(isset($content->twitch_login)) $content_dto->twitch_login = $content->twitch_login;
        if(isset($content->audience)) $content_dto->audience = Audience::fromValue($content->audience);
        if(isset($content->category_slug)) $content_dto->category_slug = $content->category_slug;
        if(isset($content->creator_id)) $content_dto->creator_id = $content->creator_id;
        if(isset($content->podcast_episodes)) $content_dto->podcast_episodes = $content->podcast_episodes;
        if(isset($content->audio_url)) $content_dto->audio_url = $content->audio_url;
        if(isset($content->rss_url)) $content_dto->rss_url = $content->rss_url;
        if(isset($content->guid)) $content_dto->guid = $content->guid;
        if(isset($content->subcategory_name)) $content_dto->subcategory_name = $content->subcategory_name;
        if(isset($content->audio_file_type)) $content_dto->audio_file_type = $content->audio_file_type;
        if(isset($content->result_index)) $content_dto->result_index = $content->result_index;

        return $content_dto;
    }


}

