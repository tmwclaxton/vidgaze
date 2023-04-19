<?php

namespace App\Helpers\OAuth;

use App\Helpers\PlatformAPIs\Dailymotion;
use Illuminate\Support\Facades\Auth;

class LogInWithDailymotion implements iLogInWith
{
    public static function logIn()
    {
        //check if user already has linked their account
        $creator = Auth::user()->creator;

        if(!$creator->dailymotion_channel_id){
            return (new Dailymotion(true))->client->getAuthorizationUrl();
        }
        else{
            abort(403, 'You have already claimed a Dailymotion channel');
        }
    }
}
