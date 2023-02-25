<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
   use HasFactory;

   //no mass assignment!
   protected $guarded = [];

   //Alphabetical order

   public function comments() {
   return $this->hasMany(CommentAward::class);
   }
   public function streams() {
      return $this->hasMany(StreamAward::class);
   }
   public function videos() {
      return $this->hasMany(VideoAward::class);
   }
    
}
