<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platforms;
use App\Helpers\SearchResultDTO;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use Carbon\Carbon;
use Dailymotion as DailymotionSDK;

class Dailymotion extends aPlatformAPI implements iPlatfromSearch
{
    public DailymotionSDK $client;

    private static array $requiredVideoFields =
        array(
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
                    'channel.name'
                );

    public function __construct($auth = false)
    {
        $dailymotion_client = new DailymotionSDK();

        $dailymotion_client->setGrantType(
            $auth ? DailymotionSDK::GRANT_TYPE_AUTHORIZATION : DailymotionSDK::GRANT_TYPE_CLIENT_CREDENTIALS,
            config('platforms.dailymotion.client_key'),
            config('platforms.dailymotion.client_secret'),
            ['email','userinfo','manage_videos','manage_playlists','manage_subscriptions','manage_likes'],
            ['redirect_uri'=>convertRedirectPathToUrl(config('platforms.dailymotion.redirect_url'))]
        );

        $this->client = $dailymotion_client;
    }
    private static function getPageTokenInfo($response, $pageToken): array
    {
        return  [
            "nextPageToken" => ($pageToken == null && $response['has_more']) ? 2 : (($response['has_more']) ? $pageToken + 1 : null),
            "prevPageToken" => ($pageToken > 1) ? $pageToken - 1 : null,
        ];
    }

    public static function getRelatedVideos(string $relatedToVideoId,  int $maxResults = 50, $pageToken = null): array
    {
        $response = (new Dailymotion())->client->get('/video/'.$relatedToVideoId.'/related',

            array(
                'fields' => self::$requiredVideoFields,
                'limit' => ($maxResults <=100) ? $maxResults : 100,
                'page' => $pageToken
            )
        );

        return [
            "pageTokenInfo" => self::getPageTokenInfo($response, $pageToken),
            "results" => self::convertResponseToDTOs($response['list'])
        ];
    }

    public static function search($searchQuery, int $maxResults = 20, $pageToken = null,  $filters = null){

        try {
            $response = (new Dailymotion())->client->get('/videos/',
                array(
                    'search' => $searchQuery,
                    'fields' => self::$requiredVideoFields,
                    'limit' => ($maxResults <= 100) ? $maxResults : 100,
                    'page' => $pageToken
                )
            );

            return [
                "pageTokenInfo" => self::getPageTokenInfo($response, $pageToken),
                "results" => self::convertResponseToDTOs($response['list'])
            ];
        }
        catch (\Exception $e){
            return [
                "pageTokenInfo" => null,
                "results" => []
            ];
        }
    }

    private static function convertResponseToDTOs(array $items): array
    {
        return array_map(function ($value){
            $result = new SearchResultDTO();

            switch ($value['item_type']){
                case  'video':
                    $result->kind = Kind::Video;
                    $result->video_id = $value['id'];
                    $result->publish_time = Carbon::createFromTimestamp($value['created_time']);
                    $result->views = $value['views_total'];
                    $result->likes = $value['likes_total']?:0;
                    $result->video_name = $value['title'];
                    $result->duration = $value['duration'];
                    $result->thumbnail_url = $value['thumbnail_720_url'];
                    $result->category_id = $value['channel'] ?? 'tv';
                    $result->category_slug = $value['channel'] ?? 'TV';
                    $result->category_description = $value['channel.description'];
                    $result->category_name = $value['channel.name'];
                    break;
                case  'channel':
                    $result->kind = Kind::Creator;
                    break;
                case  'playlist':
                    $result->kind = Kind::Playlist;
                    $result->playlist_id = $value['id']['playlistId'];
                    break;
            }
            $result->description = $value['description'];
            $result->channel_id = $value['owner.id'];
            $result->channel_name = $value['owner.screenname'];
            $result->platform = Platforms::Dailymotion;

            return  $result;
        }, (array) $items);
    }

    public static function getChannel(string $id)
    {
        $api = new Dailymotion();
        return $api->client->get('/user/'.$id, [
            'fields' => [
                'description',
                'cover_url',
                'screenname',
                'avatar_720_url',
                'country',
                'language',
            ]
        ]);
    }

    public static function makeCreatorModel(string $id): \Illuminate\Database\Eloquent\Model|Creator
    {
        $response = self::getChannel($id);

        $creator = Creator::firstOrNew([
            'slug' => 'dm_'.$id,
        ],[
            'name' => $response['screenname'],
            'avatar_url' => $response['avatar_720_url'],
            'banner_url' => $response['cover_url'],
            'bio' => json_encode($response['description']),
            'region' => $response['country'],
            'language' => $response['language'],
        ]);

        $source = CreatorSource::firstOrNew([
            'source_name' => Platforms::Dailymotion->name,
            'external_channel_id' => $id,
        ],[
            'creator_id' => $creator->id,
        ]);

        if($source->creator_id == $creator->id){
            $creator->save();
            $source->creator_id = $creator->id;
            $source->save();
        }
        return $creator;
    }

//    public static function makeVideoModel(string $id): \Illuminate\Database\Eloquent\Model|Video
//    {
//        $response = self::getVideo($id);
//        $creator = Creator::findOrCreate($response['owner.id'], Platforms::Dailymotion);
//
//        $video = Video::firstOrNew([
//            'creator_id' => $creator->id,
//            'preferred_source' => Platforms::Dailymotion->name,
//            'title' => $response['title'],
//            'description' => $response['description'],
//            'duration' => $response['duration'],
//            //'category_id' => ,
//            'tags' => json_encode($response['tags']),
//            'time_published' => Carbon::parse($response['created_time']),
//            'thumbnail_url' => $response['thumbnail_720_url'],
//            'language' => $response['language'],
//        ],[
//            'slug' => generateRandomString(),
//        ]);
//
//        $source = VideoSource::firstOrNew([
//            'source_name' => Platforms::Dailymotion->name,
//            'external_id' => $id,
//        ],[
//            'video_id' => $video->id,
//        ]);
//
//        if($source->video_id == $video->id){
//            $video->save();
//            $source->video_id = $video->id;
//            $source->save();
//        }
//        return $video;
//    }

    public static function getVideo(string $id)
    {
        $api = new Dailymotion();
        return $api->client->get('/video/'.$id, [
            'fields' => [
                'owner.id',
                'title',
                'thumbnail_720_url',
                'created_time',
                'duration',
                'views_total',
                'likes_total',
                'description',
                'language',
                'tags',
                'channel',
                'channel.description'
            ]
        ]);
    }

    //DON'T run this on channels with 600+ videos, it will time out and max out your CPU and RAM
    public static function getAllChannelVideosContent(string $id) : array //SearchResultDTO
    {
        $results = [];
        $lastPublishedAt = null;
        $hasNext = true;
        //$count = 20;
        while($hasNext){
            $content = self::getChannelVideosBeforeDate($id, $lastPublishedAt,100);
            $results = array_unique(array_merge($results, $content['results']),SORT_REGULAR);
            $lastPublishedAt = $content['lastPublishedAt'];
            $hasNext = $content['hasNext'];
            //($count==1)?$hasNext=false:$count--;
        }
        return $results;
    }

    public static function getChannelVideosBeforeDate(string $id, Carbon | null $date = null, $maxResults = 100): array  //SearchResultDTO
    {
        $api = new Dailymotion();
        $queryParams = [
            'fields' => self::$requiredVideoFields,
            'created_before' => $date?->timestamp,
            'limit' => $maxResults
        ];
        $response = $api->client->get('/user/'.$id.'/videos', $queryParams);

        $results = array_map(function($value){

            $result = new SearchResultDTO();

            $result->kind = Kind::Video;
            $result->video_id = $value['id'];
            $result->video_name = $value['title'];
            $result->duration = $value['duration'];
            $result->publish_time = Carbon::createFromTimestamp($value['created_time']);
            $result->thumbnail_url = $value['thumbnail_720_url'];
            $result->views = $value['views_total'];
            $result->likes = $value['likes_total']?:0;
            $result->channel_id = $value['owner.id'];
            $result->channel_name = $value['owner.screenname'];
            $result->platform = Platforms::Dailymotion;

            return $result;
        }, $response['list']);
        return [
            'lastPublishedAt' => Carbon::createFromTimestamp(end($response['list'])['created_time']),
            'hasNext' => boolval($response['has_more']),
            'results' => $results,
        ];
    }

    public static function updateChannelVideosBeforeDate($id, null | Carbon $date = null, $maxResults = 100): array
    {
        $response = self::getChannelVideosBeforeDate($id, $date, $maxResults);
        SearchResultDTO::convertResultDTOToModels($response['results']);
        return [
            'lastPublishedAt' => $response['lastPublishedAt'],
            'hasNext' => $response['hasNext'],
        ];
    }

    public static function getCategories() : array //SearchResultDTO
    {
        $api = new Dailymotion();
        $response = $api->client->get('/channels', [
            'fields' => [
                'id',
                'description',
                'name',
                'slug'
            ]
        ]);

        $results = array_map(function ($value){

            $DTO = new SearchResultDTO();
            $DTO->category_id = $value['id'];
            $DTO->description = $value['description'];
            $DTO->kind = Kind::Category;
            $DTO->platform = Platforms::Dailymotion;
            $DTO->category_name = $value['name'];
            $DTO->assignable = true;
            $DTO->category_slug = $value['slug'];
            return $DTO;
        }, $response['list']);
        return $results;
    }

}
