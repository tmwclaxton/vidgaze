<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanLogin;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use Illuminate\Support\Arr;
use TwitchApi\HelixGuzzleClient;
use TwitchApi\TwitchApi;

class AuthTwitch extends Twitch implements iCanLogin
{

    public $access_token;

    public function __construct($access_token = null)
    {
        $this->access_token = $access_token;
        $helixGuzzleClient = new HelixGuzzleClient(config('platforms.twitch.client_id'));
        $this->client = new TwitchApi($helixGuzzleClient, config('platforms.twitch.client_id'), config('platforms.twitch.client_secret'));
    }

    public static function getAccessTokenWithCode($code): array
    {
        $twitch_oauth = resolve(Twitch::class)->client->getOauthApi();
        $tokens = json_decode($twitch_oauth->getUserAccessToken($code,convertRedirectPathToUrl(strval(config('platforms.twitch.redirect_url'))))->getBody()->getContents());

        return [
            'access_token' => $tokens->access_token,
            'refresh_token' => $tokens->refresh_token,
            'expires_in' => $tokens->expires_in,
        ];
    }

    public function getMyCreator(): CreatorDTO
    {
        $response = resolve(AuthTwitch::class)->client->getUsersApi()->getUserByAccessToken($this->access_token);
        // Get and decode the actual content sent by Twitch.
        $responseContent = json_decode($response->getBody()->getContents())->data[0];

        $creatorDTO = new CreatorDTO(Platform::Twitch, $responseContent->id);
        $creatorDTO->twitch_login = $responseContent->login;
        $creatorDTO->name = $responseContent->display_name;
        $creatorDTO->avatar_url = $responseContent->profile_image_url;
        $creatorDTO->description = $responseContent->description;
        return $creatorDTO;
    }

    public static function getLogInUrl(array $scopes = null, string $redirect_url_path = null){
        //check if user already has linked their account
        $creator = auth()->user()->creator()->with('sources')->first();
        if(!$creator){
            abort(403, 'You must be logged in to link your Twitch account');
        }
        if(!$creator->sources->contains('source_name', Platform::Twitch->value)){
            $scopes = ['channel:read:stream_key','channel:manage:videos','user:read:subscriptions','user:edit'];
            $scopes = implode ("%20", $scopes);
            return resolve(Twitch::class)->client->getOauthApi()->getAuthUrl(convertRedirectPathToUrl(config('platforms.twitch.redirect_url')), 'code', $scopes);
        }
        else{
            abort(403, 'You have already claimed a Twitch channel');
        }
    }

    public static function getRefreshAccessToken($refreshToken): array
    {
        return [];
//        return [
//            'access_token' => $access_token['access_token'],
//            'refresh_token' => $access_token['refresh_token'],
//            'expires_in' => $access_token['expires_in'],
//        ];
    }


}
