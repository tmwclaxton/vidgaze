<?php

namespace App\Models\VideoModels;

use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoDisinterest extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = ['id'];

    public function owner() {
        return $this->belongsTo(Creator::class, 'creator_id');
    }
}
