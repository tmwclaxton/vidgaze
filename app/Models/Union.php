<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Union extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    //Alphabetical order

    public function members() {
        return $this->hasMany(Creator::class, 'id')
        ->join('union_memberships', 'union_memberships.member_id', '=', 'creators.id');
    }
    public function owner() {
        return $this->belongsTo(Creator::class);
    }
    
}
