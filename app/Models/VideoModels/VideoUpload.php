<?php

namespace App\Models\VideoModels;

use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoUpload extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    public function creator() {
        return $this->belongsTo(Creator::class);
    }
}
