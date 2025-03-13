<?php

namespace App\Models;

use App\Models\CreatorModels\Creator;
use App\Models\StreamModels\Stream;
use App\Models\VideoModels\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // TODO: no mass assignment!
    protected $guarded = ['id'];



    public function creators() {
        return $this->hasMany(Creator::class);
    }
    public function streams() {
        return $this->hasMany(Stream::class);
    }
    public function videos() {
        return $this->hasMany(Video::class);
    }
}
