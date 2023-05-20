<?php

namespace App\Models;

use App\Models\CreatorModels\Creator;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    //no mass assignment!
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function creator(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Creator::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isAdmin(): bool
    {
        $adminEmails = config('admins.emails');

        //check if email is verified
        if (!$this->email_verified_at) {
            return false;
        }

        //check if email is in array of admins
        if (!in_array($this->email, $adminEmails)) {
            return false;
        }

        //auth check, this is redundant but I like to be explicit
        if (!auth()->check()) {
            return false;
        }

        return true; //if all checks pass, return true
    }

}
