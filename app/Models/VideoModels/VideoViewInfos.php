<?php

namespace App\Models\VideoModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoViewInfos extends Model
{
    //no mass assignment!
    protected $guarded = ['id'];
    use HasFactory;
}
