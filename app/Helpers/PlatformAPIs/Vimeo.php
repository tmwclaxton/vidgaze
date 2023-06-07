<?php

namespace App\Helpers\PlatformAPIs;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Vimeo\Vimeo as VimeoSDK;


class Vimeo
{

    public VimeoSDK $client;
    public function __construct()
    {
        $this->client = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
    }

    public function getCreator(string $id)
    {
        $fields = implode(',', [
            'uri',
            'body',
            'name',
            'pictures',
            'bio',
            'location_details',
        ]);
        $data = $this->client->request('/users/' . $id . '?fields=' . $fields)['body'];

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
        try {
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
                $contentDTO->tags = array_map(fn($item)=>$item['name'],$value['tags']);
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

//            return [
//                "pageTokenInfo" => self::getPageTokenInfo($response, $pageToken),
//                "results" => self::convertResponseToDTOs($items)
//            ];
        }
        catch (\Exception $e){
            return [
                "pageTokenInfo" => null,
                "results" => []
            ];
        }
    }
}
