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

class Twitch implements iSearchable, iIsPlatform, iCanLogin
{

    public TwitchApi $client;
    public $access_token;

    public function __construct($access_token = null)
    {
        if(isset($access_token)) $this->access_token = $access_token;
        $helixGuzzleClient = new HelixGuzzleClient(config('platforms.twitch.client_id'));
        $this->client = new TwitchApi($helixGuzzleClient, config('platforms.twitch.client_id'), config('platforms.twitch.client_secret'));
    }


    public function getMyCreator(): CreatorDTO
    {
        $response = resolve(Twitch::class)->client->getUsersApi()->getUserByAccessToken($this->access_token);
        // Get and decode the actual content sent by Twitch.
        $responseContent = json_decode($response->getBody()->getContents())->data[0];

        $creatorDTO = new CreatorDTO(Platform::Twitch, $responseContent->id);
        $creatorDTO->twitch_login = $responseContent->login;
        $creatorDTO->name = $responseContent->display_name;
        $creatorDTO->avatar_url = $responseContent->profile_image_url;
        $creatorDTO->description = $responseContent->description;
        return $creatorDTO;
    }

    public static function getPlatform(): Platform
    {
        return Platform::Twitch;
    }

    public function getAppBearerToken(){
        $token = $this->client->getOauthApi()->getAppAccessToken();
        $data = json_decode($token->getBody()->getContents());

        // Your bearer token
        return $data->access_token ?? null;
    }

    // max 100 ids
    public function getCreators(array $ids): array
    {
        // validate ids
        if (count($ids) > 100) {
            throw new \Exception('Too many ids, max 100');
        }

        $api = $this->client->getUsersApi();
        $data = json_decode($api->getUsers($this->getAppBearerToken(), $ids)->getBody()->getContents())->data;

        return Arr::map($data,
            function ($value) {
                $creatorDTO = new CreatorDTO(Platform::Twitch, $value->id);
                $creatorDTO->twitch_login = $value->login;
                $creatorDTO->name = $value->display_name;
                $creatorDTO->avatar_url = $value->profile_image_url;
                $creatorDTO->description = $value->description;
                return $creatorDTO;
            });
    }

    public static function search(SearchQueryDTO $searchQueryDTO): array
    {
        $tw = new self();

        $api = $tw->client->getSearchApi();
        $data = json_decode($api->searchChannels(
            $tw->getAppBearerToken(),
            $searchQueryDTO->query,
            null,
            $searchQueryDTO->max_results
        )->getBody()->getContents())->data;

        return Arr::map($data,
            function ($value) {
                $creatorDTO = new CreatorDTO(Platform::Twitch, $value->id);
                $creatorDTO->id = $value->id;
                $creatorDTO->twitch_login = $value->broadcaster_login;
                $creatorDTO->name = $value->display_name;
//                $creatorDTO->description = $value->description;
                $creatorDTO->language = $value->broadcaster_language;
                $creatorDTO->is_live = $value->is_live;
                $creatorDTO->avatar_url = $value->thumbnail_url;
//                $creatorDTO->category = $value->game_id; ->game_name

                $resultDTO = new ResultDTO(Platform::Twitch, Kind::Creator);
                $resultDTO->creator = $creatorDTO;
                return $resultDTO;
            });

//         remove null values (ie banned accounts)
//        $results = array_filter($results, function($value) {
//            return $value !== null;
//        });
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
