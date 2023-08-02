<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Models\Category;
use Carbon\Carbon;

class UploadDTO
{

    public string $video_path;
    public string $title;
    public string $description;
    public array $tags;
    public Category $category;
    public string $language;
    public string $region;
    public string $thumbnail_path;
    public string $creator_id;
    public array $platforms;
    public Audience $audience;
    public Visibility $visibility;
    public Carbon $publish_time;

    public function __construct(string $video_path, string $title, string $description, string $creator_id, array $platforms, string $thumbnail_path, array $tags, Category $category, Visibility $visibility, Audience $audience, Carbon $publish_time)
    {
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
