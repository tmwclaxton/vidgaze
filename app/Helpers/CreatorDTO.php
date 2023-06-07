<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;
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

}
