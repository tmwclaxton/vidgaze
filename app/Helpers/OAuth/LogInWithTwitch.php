<?php

namespace App\Helpers\OAuth;

use App\Helpers\PlatformAPIs\Twitch;
use Illuminate\Support\Facades\Auth;

class LogInWithTwitch implements iLogInWith
{
    public static function logIn()
    {
        //check if user already has linked their account
        $creator = Auth::user()->creator;
        if(!$creator->twitch_channel_id){
            $scopes = ['channel:read:stream_key','channel:manage:videos','user:read:subscriptions','user:edit'];
            $scopes = implode ("%20", $scopes);
            return resolve(Twitch::class)->client->getOauthApi()->getAuthUrl(convertRedirectPathToUrl(config('platforms.twitch.redirect_url')), 'code', $scopes);
        }
        else{
            abort(403, 'You have already claimed a Twitch channel');
        }
    }
}
