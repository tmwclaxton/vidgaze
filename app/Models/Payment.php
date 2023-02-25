<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','name','stripe_id','subtotal','tax','total','receipt_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
