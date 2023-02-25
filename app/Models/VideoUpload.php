<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoUpload extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    public function creator() {
        return $this->belongsTo(Creator::class);
    }
}
