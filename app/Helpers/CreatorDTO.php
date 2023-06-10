<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use Carbon\Carbon;

class CreatorDTO
{

    public Kind $kind = Kind::Creator;
    public string $id;
    public string $name;
    public string | null $avatar_url;
    public string | null $banner_url;
    public string | null $description;
    public string $twitch_login;
    public bool $is_live;
    public Platform $platform;
    public string | null $region;
    public string | null $language ;

    public function __construct(Platform $platform, string $id)
    {
        $this->platform = $platform;
        $this->id = $id;
    }

    public static function convertFromArray($creator)
    {
    }

    public function save()
    {
        $var = CreatorSource::where('source_name', '=', $this->platform->value)
            ->where('external_channel_id', '=', $this->id)
            ->firstOr(function () {
                $creator = Creator::create([
                    'slug' => $this->platform->getPrefix().'_'.$this->id,
                    'name' => $this->name,
                    'avatar_url' => $this->avatar_url ?? null,
                    'banner_url' => $this->banner_url ?? null,
                    'description' => $this->description ?? null,
                    'region' => $this->region ?? null,
                    'language' => $this->language ?? null,
                    'is_live' => $this->is_live ?? null,
                ]);
                CreatorSource::create([
                    'source_name' => $this->platform->value,
                    'external_channel_id' => $this->id,
                    'creator_id' => $creator->id
                ]);
                return $creator;
            });
        return $var instanceof Creator? $var : $var->creator()->first();
    }

    public static function convertFromStdClass($creator): CreatorDTO
    {
        $creator_dto = new self(Platform::fromValue($creator->platform), $creator->id);
        $creator_dto->name = $creator->name;
        if(isset($creator->avatar_url)) $creator_dto->avatar_url = $creator->avatar_url;
        if(isset($creator->banner_url)) $creator_dto->banner_url = $creator->banner_url;
        if(isset($creator->description)) $creator_dto->description = $creator->description;
        if(isset($creator->twitch_login)) $creator_dto->twitch_login = $creator->twitch_login;
        if(isset($creator->is_live)) $creator_dto->is_live = $creator->is_live;
        if(isset($creator->region)) $creator_dto->region = $creator->region;
        if(isset($creator->language)) $creator_dto->language = $creator->language;
        return $creator_dto;
    }
}
