<?php

namespace App\Models\StreamModels;

use App\Models\Award;
use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamAward extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];


    protected $with = ['award'];


    //Alphabetical order

    public function award() {
        return $this->hasOne(Award::class, 'id','award_id');
    }
    public function stream() {
        return $this->belongsTo(Stream::class);
    }
    public function giver() {
        return $this->hasOne(Creator::class, 'id', 'giver_id');
    }
}
