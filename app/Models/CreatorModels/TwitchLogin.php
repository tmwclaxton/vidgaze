<?php

namespace App\Models\CreatorModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwitchLogin extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    protected $with = ['source'];

    public function source(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CreatorSource::class, 'external_channel_id', 'twitch_source_id');
    }
}
