<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentDislike extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    //Alphabetical order

    public function comment() {
        return $this->belongsTo(Comment::class);
    }
    public function creator() {
        return $this->hasOne(Creator::class);
    }
}
