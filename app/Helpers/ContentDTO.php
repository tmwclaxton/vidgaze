<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;
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
    public bool $explicit;

    //    Podcast Specific Variables
    public string $audio_url;
    public string $rss_url;
    public string $guid;
    public string $subcategory_name;
    public string $audio_file_type;
    public int $result_index;
    public string $id;


    public function __construct(Platform $platform, Kind $kind, string $id)
    {
        $this->platform = $platform;
        $this->kind = $kind;
        $this->id = $id;
    }
}

