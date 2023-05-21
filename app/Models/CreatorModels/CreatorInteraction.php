<?php

namespace App\Models\CreatorModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorInteraction extends Model
{
    use HasFactory;

    public function owner() {
        return $this->belongsTo(Creator::class, 'creator_id');
    }
}
