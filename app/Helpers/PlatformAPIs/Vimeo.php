<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platforms;
use App\Helpers\SearchResultDTO;
use App\Models\Creator;
use App\Models\CreatorSource;
use App\Models\Video;
use App\Models\VideoSource;
use Carbon\Carbon;
use Vimeo\Vimeo as VimeoSDK;

class Vimeo extends aPlatformAPI implements iPlatfromSearch
{
    public $client;
    private static array $requiredVideoFields =
        [
            'uri',
            'name',
            'description',
            'duration',
            'language',
            'tags',
            'release_time',
            'user',
            'pictures',
            '', //keep this blank space, Vimeo are stupid
        ];

    private static function getRequiredVideoFields(): string
    {
        return implode(',',self::$requiredVideoFields);
    }

    public function __construct()
    {
        $this->client = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
    }


    public static function getRelatedVideos(string $relatedToVideoId,  int $maxResults = 50, $pageToken = null): array
    {
        $response = (new Vimeo())->client->request('/videos/'.$relatedToVideoId.'/videos',

            array(
                'filter' => 'related',
                'per_page' => ($maxResults <=100) ? $maxResults : 100,
                'page' => $pageToken
            )
        );

        return [
            "pageTokenInfo" => self::getPageTokenInfo($response, $pageToken),
            "results" => self::convertResponseToDTOs($response['body']['data'])
        ];
    }

    private static function getPageTokenInfo($response, $pageToken): array
    {
        return [
            "nextPageToken" => ($pageToken == null && $response['body']['paging']['next']) ? 2 : (($response['body']['paging']['next']) ? $pageToken + 1 : null),
            "prevPageToken" => ($pageToken > 1) ? $pageToken - 1 : null,
        ];
    }

    public static function search($searchQuery, int $maxResults = 20, $pageToken = null, $filters = null)
    {
        try {
            $response = (new Vimeo)->client->request('/videos', [
                'query' => $searchQuery,
                'per_page' => ($maxResults <= 100) ? $maxResults : 100,
                'page' => $pageToken,
            ]);
            $items = $response['body']['data'];
            return [
                "pageTokenInfo" => self::getPageTokenInfo($response, $pageToken),
                "results" => self::convertResponseToDTOs($items)
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
        return array_map(function ($value) {
            $result = new SearchResultDTO();

            switch ($value['type']){
                case  'video':
                case 'live':
                    $result->kind = Kind::Video;
                    $result->video_id = str_replace("/videos/", "", $value['uri']);
                    $result->publish_time = $value['release_time'];
                    $result->video_name = $value['name'];
                    $result->duration = $value['duration'];
                    $result->thumbnail_url = $value['pictures']['base_link'];
                    $result->tags = array_map(fn($item)=>$item['name'],$value['tags']);
                    break;
                case  'channel':
                    $result->kind = Kind::Creator;
                    break;
                case  'playlist':
                    $result->kind = Kind::Playlist;
                    $result->playlist_id = $value['id']['playlistId'];
            }
            $result->description = $value['description'];
            $result->channel_id = str_replace("/users/", "", $value['user']['uri']);
            $result->channel_name = $value['user']['name'];
            $result->platform = Platforms::Vimeo;

            return $result;

        }, (array)$items);
    }
    public static function getChannel(string $id): array
    {
        $api = new Vimeo();
        $fields = implode(',', [
            'body',
            'name',
            'pictures',
            'bio',
            'location_details',
        ]);
        return $api->client->request('/users/' . $id . '?fields=' . $fields)['body'];
    }

    public static function makeCreatorModel(string $id): \Illuminate\Database\Eloquent\Model|Creator
    {
        $response = self::getChannel($id);

        $creator = Creator::firstOrNew([
            'slug' => 'v_' . $id,
        ],[
            'name' => $response['name'],
            'avatar_url' => $response['pictures']['base_link'],
            'bio' => json_encode($response['bio']),
            //'region' => $response['location_details']['country_iso_code'],
        ]);

        $source = CreatorSource::firstOrNew([
            'source_name' => Platforms::Vimeo->name,
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

    public static function getVideo(string $id) : array
    {
        $api = new Vimeo();
        return $api->client->request('/videos/' . $id . '?fields=' . self::getRequiredVideoFields())['body'];
    }

    public static function makeVideoModel(string $id): \Illuminate\Database\Eloquent\Model|Video
    {
        $response = self::getVideo($id);

        $creator = Creator::findOrCreate(str_replace("/users/", "", $response['user']['uri']), Platforms::Vimeo);

        $video = Video::firstOrNew([
            'creator_id' => $creator->id,
            'preferred_source' => Platforms::Vimeo->name,
            'title' => $response['name'],
            'description' => $response['description'],
            'duration' => $response['duration'],
            //'category_id' => ,
            'tags' => json_encode(array_map(fn($item)=>$item['name'],$response['tags'])),
            'time_published' => Carbon::parse($response['release_time']),
            'thumbnail_url' => $response['pictures']['base_link'],
            //'language' => $response['language'],
        ],[
            'slug' => generateRandomString(),
        ]);

        $source = VideoSource::firstOrNew([
            'source_name' => Platforms::Vimeo->name,
            'external_id' => $id,
        ],[
            'video_id' => $video->id,
        ]);

        if($source->video_id == $video->id){
            $video->save();
            $source->video_id = $video->id;
            $source->save();
        }
        return $video;
    }

    public static function getChannelVideos(string $id, int $page = 1, $maxResults = 100): array //SearchResultDTO
    {
        $api = new Vimeo();
        $response = $api->client->request('/users/' . $id . '/videos' . '?fields=' . self::getRequiredVideoFields(),[
            'sort'=>'date',
            'per_page' => 60,
            'page' => $page,
        ])['body'];

        $results = array_map(function($value){
            $result = new SearchResultDTO();

            $result->kind = Kind::Video;
            $result->video_id = str_replace("/videos/", "", $value['uri']);
            $result->video_name = $value['name'];
            $result->duration = $value['duration'];
            $result->publish_time = Carbon::make($value['release_time']);
            $result->thumbnail_url = $value['pictures']['base_link'];
            $result->channel_id = str_replace("/users/", "", $value['user']['uri']);
            $result->channel_name = $value['user']['name'];
            $result->platform = Platforms::Vimeo;
            $result->tags = array_map(fn($item)=>$item['name'],$value['tags']);

            return $result;
        }, $response['data']);

        return [
            'nextPage' => $response['page']++,
            'hasNext' => boolval($response['paging']['next']),
            'results' => $results,
        ];
    }

    public static function updateChannelVideos($id, int $page = 1, $maxResults = 100): array
    {
        $response = self::getChannelVideos($id, $page, $maxResults);
        SearchResultDTO::convertResultDTOToModels($response['results']);
        return [
            'nextPage' => $response['nextPage'],
            'hasNext' => $response['hasNext'],
        ];
    }

    public static function getCategories() : array //SearchResultDTO
    {
        $api = new Vimeo();
        $fields = implode(',', [
//            'body',
//            'name',
//            'pictures',
//            'bio',
//            'location_details',
        ]);
        $response = $api->client->request('/channels/music?fields=' . $fields)['body'];

//        $response = $api->client->request('/users/39723100/channels?fields=name')['body']['data'];
//
//        dd(array_map(fn($value)=>$value['name'],$response));
        dd($response);


        $fields = implode(',', [
            'body',
            'name',
            'pictures',
            'bio',
            'location_details',
        ]);
        return $api->client->request('/users/' . $id . '?fields=' . $fields)['body'];

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

