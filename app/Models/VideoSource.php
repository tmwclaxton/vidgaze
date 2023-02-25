<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoSource extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    public function video(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Video::class, 'id', 'video_id');
    }
}
