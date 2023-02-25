<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveClient extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'live_viewer_counted' => 'boolean',
        'view_counted' => 'boolean',
    ];
}
