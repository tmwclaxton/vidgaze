<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwitchLogin extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    public function source(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CreatorSource::class, 'external_channel_id', 'twitch_source_id');
    }
}
