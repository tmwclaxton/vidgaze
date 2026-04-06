<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Models\CreatorModels\Creator;
use App\Models\PodcastModels\Podcast;
use App\Models\StreamModels\Stream;
use App\Models\VideoModels\Video;

class ResultDTO
{
    public CreatorDTO $creator;

    public ContentDTO $content;

    public Platform|string $platform;

    public Kind $kind;

    public function __construct(Platform $platform, Kind $kind)
    {
        $this->platform = $platform;
        $this->kind = $kind;
    }

    public function save(): Creator|Video|Stream|Podcast
    {
        $creator = $this->creator->save();
        if ($this->kind === Kind::Creator) {
            return $creator;
        }
        if ($this->kind === Kind::Podcast) {
            return $this->content->savePodcast($creator->id);
        }

        return $this->content->save($creator->id);
    }

    public static function saveAll(array $results): array
    {
        $models = [];
        foreach ($results as $result) {
            $models[] = $result->save();
        }

        return $models;
    }

    public static function convertArray(array $results): array
    {
        $result_dtos = [];
        foreach ($results as $result) {
            $result_dtos[] = self::convertFromStdClass($result);
        }

        return $result_dtos;
    }

    public static function convertFromStdClass($result): ResultDTO
    {
        $result_dto = new self(Platform::fromValue($result->platform), Kind::fromValue($result->kind));
        if (isset($result->creator)) {
            $result_dto->creator = CreatorDTO::convertFromStdClass($result->creator);
        }
        if (isset($result->content)) {
            $result_dto->content = ContentDTO::convertFromStdClass($result->content);
        }

        return $result_dto;
    }
}
