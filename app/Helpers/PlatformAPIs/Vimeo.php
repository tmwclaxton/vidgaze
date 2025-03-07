<?php

namespace App\Helpers\PlatformAPIs;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Vimeo\Vimeo as VimeoSDK;


class Vimeo implements iSearchable, iIsPlatform
{

    public VimeoSDK $client;
    public function __construct()
    {
        $this->client = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
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
        return self::extractedContentAndUserDtoMap($response['body']['data']);


    }

    public static function getCreatorVideos(string $id, int $page = null, $maxResults = 100): array //SearchResultDTO
    {
        if($maxResults > 100) throw new \Exception('Max results cannot be greater than 100');

        $page = $page ?? 1;
        $api = new Vimeo();
        $response = $api->client->request('/users/' . $id . '/videos',[
            'sort'=>'date',
            'per_page' => $maxResults,
            'page' => $page,
            'fields' => 'uri,name,description,duration,release_time,pictures,tags,user',
        ])['body'];

        if(!isset($response['data'])) {
            return [
                'next' => $page + 1,
                'hasNext' => false,
                'results' => [], // ContentDTO
            ];
        }

        $results = self::returnContentDTOarrayMap($response['data']);

        return [
            'next' => $page + 1,
            'hasNext' => boolval($response['paging']['next']),
            'results' => $results, // ContentDTO
        ];
    }

    // grab featured videos
    public static function getFeaturedVideos(int $maxResults = 10): array
    {
        $api = new Vimeo();
//        $response = $api->client->request('/videos?filter=trending&direction=desc',[
//            'per_page' => $maxResults,
//            'fields' => 'uri,name,description,duration,release_time,pictures,tags,user',
//        ]);

        // /videos?direction=desc&fields=%5Buri%2Cname%2Cdescription%2Cduration%2Crelease_time%2Cpictures%2Ctags%2Cuser%5D&filter=trending&per_page=10&sort=date&page=1
        $response = $api->client->request('/videos',[
            'filter' => 'trending',
            'sort' => 'date',
            'direction' => 'desc',
            'per_page' => $maxResults,
            'fields' => 'uri,name,description,duration,release_time,pictures,tags,user',
        ]);

        return self::extractedContentAndUserDtoMap($response['body']['data']);
    }

    /**
     * @param $data
     * @return ContentDTO[]
     */
    public static function returnContentDTOarrayMap($data): array
    {
        $results = array_map(function ($value) {
            $contentDTO = new ContentDTO(Platform::Vimeo, Kind::Video, str_replace("/videos/", "", $value['uri']));

            $contentDTO->kind = Kind::Video;
            $contentDTO->name = $value['name'];
            $contentDTO->duration = $value['duration'];
            $contentDTO->publish_time = Carbon::make($value['release_time']);
            $contentDTO->thumbnail_url = $value['pictures']['base_link'];
            $contentDTO->creator_id = str_replace("/users/", "", $value['user']['uri']);
            $contentDTO->tags = array_map(fn($item) => $item['name'], $value['tags']);

            return $contentDTO;
        }, $data);
        return $results;
    }

    /**
     * @param $data
     * @return array
     */
    public static function extractedContentAndUserDtoMap($data): array
    {
        $items = $data;

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
                return $item['name'] ?? false;
            });
            $contentDTO->tags = array_map(fn($item) => $item['name'], $value['tags']);
            $contentDTO->description = $value['description'];
            $contentDTO->creator_id = str_replace("/users/", "", $value['user']['uri']);

            $creatorDTO->name = $value['user']['name'];
            $creatorDTO->description = $value['user']['bio'] ?? "";
            $creatorDTO->avatar_url = end($value['user']['pictures']['sizes'])['link'];

            $resultDTO->content = $contentDTO;
            $resultDTO->creator = $creatorDTO;
            return $resultDTO;
        });
    }
}
