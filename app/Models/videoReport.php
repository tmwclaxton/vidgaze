<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class videoReport extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = ['id'];

    public function owner() {
        return $this->belongsTo(Creator::class, 'creator_id');
    }
}
