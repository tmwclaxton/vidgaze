<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Models\CreatorModels\Creator;
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

    public function save(){
        return Creator::firstOrCreate([
            'source_name' => $this->platform->value,
            'external_channel_id' => $this->id,
        ],[
            'creator_id' => Creator::create([
                'slug' => $this->platform->getPrefix().'_'.$this->id,
                'name' => $this->name,
                'avatar_url' => $this->avatar_url,
                'banner_url' => $this->banner_url,
                'description' => $this->description,
                'region' => $this->region,
                'language' => $this->language,
                'is_live' => $this->is_live,
            ])
        ])->creator()->first();
    }

}
