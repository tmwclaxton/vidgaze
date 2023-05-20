<?php

namespace App\Models\PodcastModels;

use App\Models\Award;
use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PodcastAward extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    protected $with = ['award'];



    //Alphabetical order

    public function award() {
        return $this->hasOne(Award::class, 'id','award_id');
    }
    public function podcast() {
        return $this->belongsTo(Podcast::class);
    }
    public function giver() {
        return $this->hasOne(Creator::class, 'id', 'giver_id');
    }
}
