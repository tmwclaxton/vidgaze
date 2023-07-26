<?php

namespace App\Models\PodcastEpisodeModels;

use App\Models\Award;
use App\Models\CreatorModels\Creator;
use App\Models\StreamModels\Stream;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PodcastEpisodeAward extends Model
{

    //no mass assignment!
    protected $guarded = [];


    protected $with = ['award'];


    //Alphabetical order

    public function award(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Award::class, 'id','award_id');
    }
    public function podcastEpisode(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PodcastEpisode::class);
    }
    public function giver(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Creator::class, 'id', 'giver_id');
    }
}
