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
use App\Helpers\Tools;
use Carbon\Carbon;
use Dailymotion as DailymotionSDK;
use Illuminate\Support\Arr;

class Dailymotion implements iSearchable, iIsPlatform
{
    public DailymotionSDK $client;

    private static array $searchFields = array(
            'id',
            'owner.id',
            'owner.screenname',
            'item_type',
            'title',
            'thumbnail_720_url',
            'created_time',
            'duration',
            'views_total',
            'likes_total',
            'channel',
            'description',
            'channel.description',
            'channel.name',
            'owner.description',
            'owner.country',
            'owner.language',
            'owner.avatar_720_url',
            'owner.cover_url',

        );
    public function __construct()
    {
        $dailymotion_client = new DailymotionSDK();

        $dailymotion_client->setGrantType(
            DailymotionSDK::GRANT_TYPE_CLIENT_CREDENTIALS,
            config('platforms.dailymotion.client_key'),
            config('platforms.dailymotion.client_secret')
        );

        $this->client = $dailymotion_client;
    }

    public static function getPlatform(): Platform
    {
        return Platform::Dailymotion;
    }

    // max ids is 100
    public function getCreators(array $ids)
    {
        // validate ids
        if (count($ids) > 100) {
            throw new \Exception('Too many ids, max 100');
        }

        $response =  $this->client->get('/users', [
            'fields' => [
                'id',
                'description',
                'cover_url',
                'screenname',
                'avatar_720_url',
                'country',
                'language',
            ],
            'ids' => implode(',', $ids),
            'limit' => 100,
        ]);

        return Arr::map($response['list'],
            function ($value) {
                $creatorDTO = new CreatorDTO(Platform::Dailymotion, $value['id']);
                $creatorDTO->description = $value['description'];
                $creatorDTO->name = $value['screenname'];
                $creatorDTO->avatar_url = $value['avatar_720_url'];
                $creatorDTO->banner_url = $value['cover_url'];
                $creatorDTO->region = $value['country'];
                $creatorDTO->language = $value['language'];
                return $creatorDTO;
            });
    }


    public static function search(SearchQueryDTO $searchQuery){

        $dm = new self();
//        try {
            $response = $dm->client->get('/videos/',
                [
                    'search' => $searchQuery->query,
                    'fields' => self::$searchFields,
                    'limit' => ($searchQuery->max_results <= 100) ? $searchQuery->max_results : 100,
//                    'page' => $pageToken
                ]
            );

//            dd($response);
            return Arr::map($response['list'], function ($item){
                $resultDTO = new ResultDTO(Platform::Dailymotion, Kind::Video);

                $contentDTO = new ContentDTO(Platform::Dailymotion, Kind::Video, $item['id']);
                $contentDTO->name = $item['title'];
                $contentDTO->description = $item['description'];
                $contentDTO->thumbnail_url = $item['thumbnail_720_url'];
                $contentDTO->duration = $item['duration'];
                $contentDTO->views = $item['views_total'];
                $contentDTO->likes = $item['likes_total'];
                $contentDTO->kind = Kind::Video;
                $contentDTO->publish_time = Carbon::parse($item['created_time']);

                $creatorDTO = new CreatorDTO(Platform::Dailymotion, $item['owner.id']);
                $creatorDTO->name = $item['owner.screenname'];
                $creatorDTO->description = $item['owner.description'];
                $creatorDTO->region = $item['owner.country'];
                $creatorDTO->language = $item['owner.language'];
                $creatorDTO->avatar_url = $item['owner.avatar_720_url'];
                $creatorDTO->banner_url = $item['owner.cover_url'];

//                $creatorDTO->avatar_url = $item['owner.avatar_720_url'];
//                $creatorDTO->banner_url = $item['owner.cover_url'];

//                DTO->description = $item['channel.description'];
                $resultDTO->content = $contentDTO;
                $resultDTO->creator = $creatorDTO;
                return $resultDTO;
            });
//            return [
//                "pageTokenInfo" => self::getPageTokenInfo($response, $pageToken),
//                "results" => self::convertResponseToDTOs($response['list'])
//            ];
//        }
//        catch (\Exception $e){
//            return [
//                "pageTokenInfo" => null,
//                "results" => []
//            ];
//        }
    }

    }
