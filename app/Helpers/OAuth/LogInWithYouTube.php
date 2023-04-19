<?php

namespace App\Helpers\OAuth;

use App\Helpers\PlatformAPIs\Google;
use Illuminate\Support\Facades\Auth;

class LogInWithYouTube implements iLogInWith
{
    public static function logIn(array $scopes = null, string $redirect_url_path = null)
    {
        //check if user already has linked their account
        $creator = Auth::user()->creator;
        if(!$creator->youtube_channel_id){
            return (new Google($scopes, $redirect_url_path))->client->createAuthUrl();
        }
        else{
            abort(403, 'You have already claimed a YouTube channel');
        }
    }
}
