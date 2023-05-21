<?php

namespace App\Models\PodcastEpisodeModels;

use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PodcastEpisodeInteraction extends Model
{
    //no mass assignment!
    protected $guarded = ['id'];
    use HasFactory;
    public function owner() {
        return $this->belongsTo(Creator::class, 'creator_id');
    }
}
