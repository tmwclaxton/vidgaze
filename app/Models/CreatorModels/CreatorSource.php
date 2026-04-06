<?php

namespace App\Models\CreatorModels;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\AuthYouTube;
use App\Helpers\PlatformAPIs\YouTube;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CreatorSource extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    public function creator(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Creator::class, 'id', 'creator_id');
    }

    public function twitchLogin(bool $returnString = true): \Illuminate\Database\Eloquent\Relations\HasOne | null | string
    {
        if ($this->source_name == Platform::Twitch->value) {
            $login = $this->hasOne(TwitchLogin::class, 'twitch_source_id', 'external_channel_id');
            return $returnString ? (($login->first())?$login->first()->twitch_channel_login: null ): $login;
        }
        return null;
    }


    public function refreshAccessToken(){
        switch ($this->source_name) {
            case Platform::YouTube->value:
                $tokens = AuthYouTube::getRefreshAccessToken($this->refresh_token);
                $this->access_token = $tokens['access_token'];
                $this->refresh_token = $tokens['refresh_token'];
                $this->save();
                return $tokens['access_token'];
            default:
                return null;
        }
    }

}
