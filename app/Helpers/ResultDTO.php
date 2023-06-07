<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;
use Carbon\Carbon;

class ResultDTO
{
    public CreatorDTO $creator;
    public ContentDTO $content;
    public Platform | string $platform;
    public Kind $kind;


    public function __construct(Platform $platform, Kind $kind)
    {
        $this->platform = $platform;
        $this->kind = $kind;
    }
}
