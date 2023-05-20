<?php

namespace App\Models\PodcastModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PodcastEpisodeViewInfo extends Model
{
    //no mass assignment!
    protected $guarded = ['id'];
    use HasFactory;
}
