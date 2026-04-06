<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanLogin;
use App\Helpers\Tools;
use Dailymotion as DailymotionSDK;

class AuthDailymotion extends Dailymotion implements iCanLogin
{

    public function __construct($auth = false)
    {
        $dailymotion_client = new DailymotionSDK();

        $dailymotion_client->setGrantType(
            $auth ? DailymotionSDK::GRANT_TYPE_AUTHORIZATION : DailymotionSDK::GRANT_TYPE_CLIENT_CREDENTIALS,
            config('platforms.dailymotion.client_key'),
            config('platforms.dailymotion.client_secret'),
            ['email','userinfo','manage_videos','manage_playlists','manage_subscriptions','manage_likes'],
            ['redirect_uri'=>Tools::convertRedirectPathToUrl(config('platforms.dailymotion.redirect_url'))]
        );

        $this->client = $dailymotion_client;
    }

    private static function getAuthApi()
    {
        $dm = new DailymotionSDK();
        $dm->setGrantType(
            DailymotionSDK::GRANT_TYPE_AUTHORIZATION,
            config('platforms.dailymotion.client_key'),
            config('platforms.dailymotion.client_secret'),
            ['email','userinfo','manage_videos','manage_playlists','manage_subscriptions','manage_likes'],
            ['redirect_uri'=>Tools::convertRedirectPathToUrl(config('platforms.dailymotion.redirect_url'))]
        );
        return $dm;
    }

    public static function getLogInUrl(array $scopes = null, string $redirect_url_path = null)
    {
        //check if user already has linked their account
        $creator = auth()->user()->creator()->with('sources')->first();
        if(!$creator){
            abort(403, 'You must be logged in to link your Dailymotion account');
        }
        if ($creator->sources->contains('source_name', Platform::Dailymotion->value)) {
            abort(403, 'You have already claimed a Dailymotion channel');
        }

        return self::getAuthApi()->getAuthorizationUrl();
    }

    public static function getRefreshAccessToken($refreshToken): array
    {
//        $dm = new Dailymotion();
//        $dm->client->refreshToken($refreshToken);
//        $access_token = $dm->client->getAccessToken();
//
//        return [
//            'access_token' => $access_token['access_token'],
//            'refresh_token' => $access_token['refresh_token'],
//            'expires_in' => $access_token['expires_in'],
//        ];
        return [];
    }

}
