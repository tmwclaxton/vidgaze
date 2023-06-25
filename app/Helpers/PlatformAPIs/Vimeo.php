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


class Vimeo implements iSearchable, iIsPlatform, iCanLogin
{

    public VimeoSDK $client;
    public string | null $access_token;
    public function __construct($code = null, $access_token = null, array $scopes = null)
    {
        $vimeo = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
        if(isset($code)){
            $access_token = $vimeo->accessToken($code, convertRedirectPathToUrl(config('platforms.vimeo.redirect_url')))['body']['access_token'];
        }
        if(isset($access_token)){
            $vimeo->setToken($access_token);
            $this->access_token = $access_token;
        }

        $this->client = $vimeo;
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

    public static function getPlatform(): Platform
    {
        return Platform::Vimeo;
    }

    public static function getCreator(string $id)
    {
        $vimeo = new self();
        $fields = implode(',', [
            'uri',
            'body',
            'name',
            'pictures',
            'bio',
            'location_details',
        ]);
        $data = $vimeo->client->request('/users/' . $id . '?fields=' . $fields)['body'];

        $creatorDTO = new CreatorDTO(Platform::Vimeo, $id);
        $creatorDTO->id = str_replace("/users/", "", $data['uri']);
        $creatorDTO->name = $data['name'];
        $creatorDTO->description = $data['bio'];
        $creatorDTO->avatar_url = end($data['pictures']['sizes'])['link'];
        $creatorDTO->region = $data['location_details']['country_iso_code'];
        return $creatorDTO;
    }

    public static function search(SearchQueryDTO $searchQueryDTO)
    {
            $response = (new Vimeo)->client->request('/videos', [
                'query' => $searchQueryDTO->query,
                'per_page' => ($searchQueryDTO->max_results <= 100) ? $searchQueryDTO->max_results : 100,
                'fields' => 'uri,name,description,duration,release_time,pictures,tags,user',
//                'page' => $pageToken,
            ]);
            $items = $response['body']['data'];

            return Arr::map($items, function ($value) {
                $resultDTO = new ResultDTO(Platform::Vimeo, Kind::Video);
                $contentDTO = new ContentDTO(Platform::Vimeo, Kind::Video,
                    str_replace("/videos/", "", $value['uri'])
                );
                $creatorDTO = new CreatorDTO(Platform::Vimeo,
                    str_replace("/users/", "", $value['user']['uri'])
                );
                $resultDTO->platform = Platform::Vimeo;

                $contentDTO->kind = Kind::Video;
                $contentDTO->publish_time = Carbon::parse($value['release_time']);
                $contentDTO->name = $value['name'];
                $contentDTO->duration = $value['duration'];
                $contentDTO->thumbnail_url = $value['pictures']['base_link'];
                // remove null values from $value['tags']

                $value['tags'] = array_filter($value['tags'], function ($item) {
                    return $item['name']?? false;
                });
                $contentDTO->tags = array_map(fn($item)=>$item['name'], $value['tags']);
                $contentDTO->description = $value['description'];
                $contentDTO->creator_id = str_replace("/users/", "", $value['user']['uri']);

                $creatorDTO->name = $value['user']['name'];
                $creatorDTO->description = $value['user']['bio']??"";
                $creatorDTO->avatar_url = end($value['user']['pictures']['sizes'])['link'];

                $resultDTO->content = $contentDTO;
                $resultDTO->creator = $creatorDTO;
                return $resultDTO;

//                switch ($value['type']){
//                    case  'video':
//                    case 'live':

//                        break;
//                    case  'channel':
//                        $DTO->kind = Kind::Creator;
//                        break;
//                    case  'playlist':
//                        $DTO->kind = Kind::Playlist;
//                        $DTO->playlist_id = $value['id']['playlistId'];
//                }


            });


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
