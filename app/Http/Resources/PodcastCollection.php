<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PodcastCollection extends ResourceCollection
{
    public $collects = PodcastResource::class;
}
