<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Models\Category;
use Carbon\Carbon;

class UploadDTO
{
    public int $video_id;
    public string $video_path;
    public string $title;
    public string | null $description;
    public array | null $tags;
    public Category $category;
    public string $language;
    public string $region;
    public string | null $thumbnail_path;
    public string $creator_id;
    public array $platforms;
    public Audience $audience;
    public Visibility $visibility;
    public Carbon | null $publish_time;

    public function __construct(int $video_id, string $video_path, string $title, string | null $description, string $creator_id, array $platforms, string | null $thumbnail_path, array | null $tags, Category $category, Visibility $visibility, Audience $audience, Carbon | null $publish_time)
    {
        $this->video_id = $video_id;
        $this->video_path = $video_path;
        $this->title = $title;
        $this->description = $description;
        $this->creator_id = $creator_id;
        $this->platforms = $this->setPlatforms($platforms);
        $this->thumbnail_path = $thumbnail_path;
        $this->tags = $tags;
        $this->category = $category;
        $this->visibility = $visibility;
        $this->audience = $audience;
        $this->publish_time = $publish_time;
    }


    public function setPlatforms(array $platforms)
    {
        return array_filter($platforms, function ($platform) {
            return in_array($platform, Platform::getUploadablePlatforms()->toArray());
        });
    }

}
