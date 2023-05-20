<?php

namespace App\Models\StreamModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamSource extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    public function stream() {
        return $this->hasOne(Stream::class, 'id','stream_id');
    }
}
