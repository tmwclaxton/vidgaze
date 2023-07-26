<?php

namespace App\Models\VideoModels;

use App\Models\Award;
use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoAward extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    protected $with = ['award'];


    public function award() {
        return $this->hasOne(Award::class, 'id','award_id');
    }
    public function video() {
        return $this->belongsTo(Video::class);
    }
    public function giver() {
        return $this->hasOne(Creator::class, 'id', 'giver_id');
    }
}
