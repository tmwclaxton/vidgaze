<?php

namespace App\Helpers\PlatformAPIs;


use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanLogin;
use App\Helpers\Tools;
use Dailymotion as DailymotionSDK;
use phpDocumentor\Reflection\Types\Self_;

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
        if(!$creator->dailymotion_channel_id){

            return self::getAuthApi()->getAuthorizationUrl();
        }
        else{
            abort(403, 'You have already claimed a Dailymotion channel');
        }
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

    public static function getAccessTokenWithCode($code)
    {
        $dm = self::getAuthApi();
//        $dm->requestAccessToken($code);
        $access_token = $dm->getAccessToken();

        return [
            'access_token' => $access_token['access_token'],
            'refresh_token' => $access_token['refresh_token'],
            'expires_in' => $access_token['expires_in'],
        ];
    }
}
