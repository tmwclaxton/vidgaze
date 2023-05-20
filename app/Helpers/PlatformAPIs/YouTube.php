<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platforms;
use App\Helpers\SearchResultDTO;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoSource;
use Carbon\Carbon;
use Google_Service_YouTube;

class YouTube extends aPlatformAPI implements iPlatfromSearch
{
    public Google_Service_YouTube $client;

    public function __construct($code = null, array $scopes = null, string $redirect_url_path = null)
    {
        $google = new Google($scopes, $redirect_url_path);
        if(isset($code)){
            $accessToken = $google->client->fetchAccessTokenWithAuthCode($code);
            $google->client->setAccessToken($accessToken);
        }
        $this->client = new Google_Service_YouTube($google->client);
    }

    //returns 15 videos per page?
    public static function getRelatedVideos(string $relatedToVideoId,  int $maxResults = 50, $pageToken = null): array
    {
        return self::search(null, $maxResults, $pageToken, null, $relatedToVideoId, 'video');
    }


    // see docs here https://developers.google.com/youtube/v3/docs/search/list
    public static function search($searchQuery, int $maxResults = 20, $pageToken = null,  $filters = null, string $relatedToVideoId = null, string $type = null){

        try {
            $client = new YouTube();
            $type = $relatedToVideoId ? 'video' : null;
            $searchQuery = $relatedToVideoId ? null : $searchQuery;

            $response = $client->client->search->listSearch(['snippet'], [
                'q' => $searchQuery,
                'pageToken' => $pageToken,
                'maxResults' => ($maxResults <= 50) ? $maxResults : 50,
                'relatedToVideoId' => $relatedToVideoId,
                'type' => $type,
            ]);

            $items = $response->getItems();

            $pageTokenInfo = [
                "nextPageToken" => $response->getNextPageToken(),
                "prevPageToken" => $response->getPrevPageToken(),
            ];

            $separate_items = [
                'creator_ids' => [],
                'video_and_stream_ids' => [],
                'playlist_ids' => [],
            ];
            foreach ($items as $item) {
                match ($item['id']['kind']) {
                    'youtube#video' => $separate_items['video_and_stream_ids'][] = $item['id']['videoId'],
                    'youtube#channel' => $separate_items['creator_ids'][] = ($item['snippet']['channelId']) ?: $item['id']['channelId'],
                    'youtube#playlist' => $separate_items['playlist_ids'][] = $item['id']['playlistId'],
                };
            }

            $results = array_merge(
                self::getVideoOrStream($separate_items['video_and_stream_ids']),
                self::getChannel($separate_items['creator_ids'], true)
            );

            return [
                "pageTokenInfo" => $pageTokenInfo,
                "results" => $results
            ];
        }
        catch (\Exception $e){
            return [
                "pageTokenInfo" => null,
                "results" => [$e->getMessage()]
            ];
        }
    }

    //YouTube can only take 50 ids at a time
    public static function getChannel(null | string | array $id , $returnDTO = false)
    {
        $channels = [];

        if($id){
            $api = new YouTube();
            if(is_array($id) && sizeof($id) > 50) {
                for ($i = 0; $i < sizeof($id) ; $i+=50) {
                    $channels = array_merge($channels, $api->client->channels->listChannels(['snippet','brandingSettings'], [
                        'id' => array_slice($id, $i, 50)
                    ])->getItems());
                }
            }
            else {
                $channels = $api->client->channels->listChannels(['snippet', 'brandingSettings'], [
                    'id' => $id
                ])->getItems();
            }

            if($returnDTO){
                $channels = array_map(function ($channel){
                    $DTO = new SearchResultDTO();
                    $DTO->kind = Kind::Creator;
                    $DTO->platform = Platforms::YouTube;
                    $DTO->channel_name = $channel->snippet->title;
                    $DTO->channel_id = $channel->id;
                    $DTO->avatar_url = $channel->snippet->thumbnails->default->url;
                    $DTO->banner_url = $channel->brandingSettings->image? $channel->brandingSettings->image->bannerExternalUrl.'=w2120-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj' : null;
                    $DTO->bio = $channel->description;
                    $DTO->region = $channel->country;
                    $DTO->language = $channel->defaultLanguage;
                    return $DTO;
                },$channels);
            }
        }
        return $channels;
    }

    public static function getVideoOrStream(null | string | array $id)
    {
        $videos = [];
        if($id){
            $api = new YouTube();
            $videos = $api->client->videos->listVideos(['snippet','contentDetails'], [
                'id' => $id
            ]);

            $videos = array_map(function ($video){
                $DTO = new SearchResultDTO();
                $DTO->kind = ($video->snippet->liveBroadcastContent == 'live') ? Kind::Stream : Kind::Video;
                $DTO->platform = Platforms::YouTube;
                $DTO->channel_id = $video->snippet->channelId;
                $DTO->video_name = $video->snippet->title;
                $DTO->stream_name = $video->snippet->title;
                $DTO->description = $video->snippet->description;
                $DTO->duration = convertYouTubeDurationToSeconds($video->contentDetails->duration);
                $DTO->tags = $video->snippet->tags ?? [];
                $DTO->publish_time = $video->snippet->publishedAt;
                $DTO->thumbnail_url = $video->snippet->thumbnails->medium->url;
                $DTO->video_id = $video->id;
                $DTO->stream_id = $video->id;
                $DTO->category_id = $video->snippet->categoryId;

                ////$DTO->region = $video->country;
                //$DTO->language = $video->defaultLanguage;
                return $DTO;
            },$videos->getItems());
        }
        return $videos;
    }

    public static function makeCreatorModel(string $id): \Illuminate\Database\Eloquent\Model|Creator
    {
        $response = self::getChannel($id)[0];
        $brandingSettings = $response->getBrandingSettings();
        $snippet = $response->getSnippet();
        $bannerUrl = $brandingSettings['image'] ? $brandingSettings['image']['bannerExternalUrl'].'=w2120-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj' : null;

        $creator = Creator::firstOrNew([
            'slug' => 'yt_'.$id,
            ],[
            'name' => $snippet['title'],
            'avatar_url' => $snippet['thumbnails']['medium']['url'],
            'banner_url' => $bannerUrl,
            'bio' => json_encode($snippet['description']),
            'region' => $snippet['country'],
//            'language' => $snippet['defaultLanguage'],
        ]);

        $source = CreatorSource::firstOrNew([
            'source_name' => Platforms::YouTube->name,
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

    //makes video model by the yt video id
    public static function makeVideoModel(string $id): \Illuminate\Database\Eloquent\Model|Video
    {
        $result = self::getVideoOrStream($id)[0];

        $creator = Creator::findOrCreate($result->channel_id, Platforms::YouTube);
        $video = Video::firstOrNew([
            'creator_id' => $creator->id,
            'preferred_source' => Platforms::YouTube->name,
            'title' => $result->video_name,
            'description' => $result->description,
            'duration' => $result->duration,
            //'category_id' => $snippet['categoryId'],
            'tags' => json_encode($result->tags),
            'time_published' => Carbon::make($result->publish_time),
            'thumbnail_url' => $result->thumbnail_url,
            //'language' => $snippet['defaultLanguage'],
        ],[
            'slug' => generateRandomString(),
        ]);

        $source = VideoSource::firstOrNew([
            'source_name' => Platforms::YouTube->name,
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

    public static function updateAllChannelContent(string $id)
    {
        SearchResultDTO::convertResultDTOToModels(self::getAllChannelVideos($id));
    }

    public static function updateChannelVideosBeforeDate($id, null | Carbon $date = null, $maxResults = 50): array
    {
        $response = self::getChannelVideosBeforeDate($id, $date, $maxResults);
        SearchResultDTO::convertResultDTOToModels($response['results']);
        return [
            'lastPublishedAt' => $response['lastPublishedAt'],
            'hasNext' => $response['hasNext'],
        ];
    }

    public static function getChannelVideosBeforeDate(string $id, Carbon | null $date, $maxResults = 50, bool $includeStreams = true, bool $onlyStreams = false): array
    {
        $api = new YouTube();

        $queryParams = [
            'channelId' => $id,
            'maxResults' => $maxResults,
            'pageToken' => null,
            'order' => 'date',
            'publishedBefore' => $date?->toISOString(),
            'type' => 'video',
            'eventType' => !$includeStreams? $event = 'completed' : ($onlyStreams? $event = 'live' : null),
        ];

        $response = $api->client->search->listSearch(['snippet'], $queryParams);
        $items = $response->getItems();
        $results = self::getVideoOrStream(array_map(fn($item)=>$item->id->videoId, $items),true);

        return [
            'lastPublishedAt' => Carbon::make(end($items)->getSnippet()->publishedAt),
            'hasNext' => boolval($response->nextPageToken),
            'results' => $results,
        ];
    }

    public static function getAllChannelVideos(string $id) : array //SearchResultDTO
    {
        $hasNext = true;
        $lastPublishedAt = null;
        $results = [];
        while($hasNext)
        {
            $content = self::getChannelVideosBeforeDate($id,$lastPublishedAt);
            $results = array_unique(array_merge($results, $content['results']),SORT_REGULAR);
            $lastPublishedAt = $content['lastPublishedAt'];
            $hasNext = $content['hasNext'];
        }
        return $results;
    }

    public static function getCategories(bool $assignable = true, string | array $id = null, $regionCode = 'us') : array //SearchResultDTO
    {
        $yt = new \App\Helpers\PlatformAPIs\YouTube();
        $queryParams = [
            'regionCode' => $regionCode
        ];
        $response = $yt->client->videoCategories->listVideoCategories('snippet',$queryParams)->getItems();

        //if the user wants to return specifically assignable or unassignable categories, then filter
        $response = isset($assignable)? array_filter($response,fn($item)=>$item->snippet->assignable): $response;
        $results = array_map(function ($value){

            $DTO = new SearchResultDTO();
            $DTO->category_id = $value->id;
            $DTO->kind = Kind::Category;
            $DTO->platform = Platforms::YouTube;
            $DTO->category_name = $value->snippet->title;
            $DTO->assignable = $value->snippet->assignable;
            $DTO->category_slug = convertNameToSlug($value->snippet->title);
            return $DTO;
            }, $response);
        return $results;
    }
}
