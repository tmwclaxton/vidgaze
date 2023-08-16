<?php

namespace App\Helpers\PlatformAPIs;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanLogin;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Vimeo\Vimeo as VimeoSDK;


class AuthVimeo extends Vimeo implements iCanLogin
{

    public $access_token;

    public function __construct($access_token){
        $this->client = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
        $this->client->setToken($access_token['access_token']);
        $this->access_token = $access_token['access_token'];
    }
    public static function getAccessTokenWithCode(string $code, array $scopes = null, string $redirect_url_path = null): array {
        $vimeo = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
        $access_token = $vimeo->accessToken($code, convertRedirectPathToUrl(config('platforms.vimeo.redirect_url')))['body']['access_token'];
        return ["access_token" => $access_token];
    }

    public function getMyCreator(): CreatorDTO
    {
        $fields = implode(',', [
            'uri',
            'body',
            'name',
            'pictures',
            'bio',
            'location_details',
        ]);
        $data = $this->client->request('/me'. '?fields=' . $fields)['body'];

        $id = str_replace("/users/", "", $data['uri']);
        $creatorDTO = new CreatorDTO(Platform::Vimeo, $id);
        $creatorDTO->id = $id;
        $creatorDTO->name = $data['name'];
        $creatorDTO->description = $data['bio'];
        $creatorDTO->avatar_url = end($data['pictures']['sizes'])['link'];
        $creatorDTO->region = $data['location_details']['country_iso_code'];
        return $creatorDTO;
    }
    public static function getLogInUrl(array $scopes = null, string $redirect_url_path = null){
        //check if user already has linked their account
        $creator = auth()->user()->creator()->with('sources')->first();
        if(!$creator){
            abort(403, 'You must be logged in to link your Vimeo account');
        }
        if(!$creator->sources->contains('source_name', Platform::Vimeo->value)){
            $scopes = ["public", "private", "create", "edit", "delete", "interact", "upload", "purchased"];
            return resolve(Vimeo::class)->client->buildAuthorizationEndpoint(convertRedirectPathToUrl(config('platforms.vimeo.redirect_url')), $scopes);
        }
        else{
            abort(403, 'You have already claimed a Vimeo channel');
        }
    }

    public static function getRefreshAccessToken($refreshToken): array
    {
        return  [];
//        return [
//            'access_token' => $access_token['access_token'],
//            'refresh_token' => $access_token['refresh_token'],
//            'expires_in' => $access_token['expires_in'],
//        ];
    }
}
