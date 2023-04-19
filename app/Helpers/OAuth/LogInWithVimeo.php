<?php

namespace App\Helpers\OAuth;


use App\Helpers\PlatformAPIs\Vimeo;
use Illuminate\Support\Facades\Auth;

class LogInWithVimeo implements iLogInWith
{
    public static function logIn()
    {
        //check if user already has linked their account
        $creator = Auth::user()->creator;
        if(!$creator->vimeo_channel_id) {
            $scopes = ["public", "private", "create", "edit", "delete", "interact", "upload", "purchased"];
            return resolve(Vimeo::class)->client->buildAuthorizationEndpoint(convertRedirectPathToUrl(config('platforms.vimeo.redirect_url')), $scopes);
        }
        else{
            abort(403, 'You have already claimed a Vimeo channel');
        }
    }
}
