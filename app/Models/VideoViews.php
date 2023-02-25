<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoViews extends Model
{    //Alphabetical order
    protected $guarded = ['id'];



    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function viewer()
    {
        return $this->hasOne(Creator::class, 'viewer_id');
    }
}
