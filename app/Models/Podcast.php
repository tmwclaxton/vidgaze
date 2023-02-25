<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function episodes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PodcastEpisode::class);
    }

}
