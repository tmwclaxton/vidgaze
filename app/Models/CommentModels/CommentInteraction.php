<?php

namespace App\Models\CommentModels;

use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentInteraction extends Model
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
