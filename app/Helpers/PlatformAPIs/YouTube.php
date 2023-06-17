<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanLogin;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\Tools;
use Carbon\Carbon;
use Google_Service_YouTube;
use Laravel\Octane\Facades\Octane;

class YouTube implements iSearchable, iIsPlatform, iCanLogin
{

    public Google_Service_YouTube $client;

    public function __construct($code = null, $access_token = null, array $scopes = null, string $redirect_url_path = null)
    {
        $google = new Google($scopes, $redirect_url_path);
        if(isset($code)){
            $accessToken = $google->client->fetchAccessTokenWithAuthCode($code);
            $google->client->setAccessToken($accessToken);
        }
        if (isset($access_token)) {
            $google->client->setAccessToken($access_token);
        }
        $this->client = new Google_Service_YouTube($google->client);
    }

    public function getMyCreator(): CreatorDTO
    {
        $data = $this->client->channels->listChannels(['snippet', 'brandingSettings'], [
            'mine' => true,
        ])->getItems()[0];

        return self::extractCreatorToDTO($data);
    }











    public static function getPlatform(): Platform
    {
        return Platform::YouTube;
    }

    //YouTube can only take 50 ids at a time
    public static function getCreators(array $ids): array
    {
        $yt = new self();
        if (!$ids) return [];
        // validate ids
        if (count($ids) > 50) {
            throw new \Exception('Too many ids, max 100');
        }

        $creators = $yt->client->channels->listChannels(['snippet', 'brandingSettings'], [
            'id' => $ids
        ])->getItems();


        return array_map(function ($creator){
            return self::extractCreatorToDTO($creator);
        },$creators);
    }



    public static function search(SearchQueryDTO $searchQueryDTO){
        $yt = new self();
        $response = $yt->client->search->listSearch(['snippet'], [
            'q' => $searchQueryDTO->query,
//            'pageToken' => $pageToken,
            'maxResults' => ($searchQueryDTO->max_results <= 50) ? $searchQueryDTO->max_results : 50,
//            'relatedToVideoId' => $relatedToVideoId,
//            'type' => $type,
        ]);

        $items = $response->getItems();
//        dd($items);
        $separate_items = [
            'creator_ids' => [], //if result is a creator
            'video_and_stream_ids' => [],
            'playlist_ids' => [],
            'all_creator_ids' => [],
        ];
        foreach ($items as $item) {
            match ($item['id']['kind']) {
                'youtube#video' => $separate_items['video_and_stream_ids'][] = $item['id']['videoId'],
                'youtube#channel' => $separate_items['creator_ids'][] = ($item['snippet']['channelId']) ?: $item['id']['channelId'],
                'youtube#playlist' => $separate_items['playlist_ids'][] = $item['id']['playlistId'],
            };
            $separate_items['all_creator_ids'][] = ($item['snippet']['channelId']) ?: $item['id']['channelId'];
        }

        $data =  Octane::concurrently([
            fn() => self::getCreators($separate_items['all_creator_ids']),
            fn() => self::getVideoOrStream($separate_items['video_and_stream_ids'], false)
        ],5000);

        // foreach video and stream, add corresponding creator to DTO
        foreach ($data[1] as $item) {
            $item->creator = $data[0][array_search($item->content->creator_id, array_column($data[0], 'id'))];
        }

        $creatorDTOs = array_filter($data[0], function ($creator) use ($separate_items) {
            return in_array($creator->id, $separate_items['creator_ids']);
        });

        $results = [];
        foreach ($creatorDTOs as $creator) {
            $resultDTO = new ResultDTO(Platform::YouTube, Kind::Creator);
            $resultDTO->creator = $creator;
            $results[] = $resultDTO;
        }
        // only return creators in the creator_ids array (and other results)
        return array_merge($results, $data[1]);
    }


    public static function getVideoOrStream(array $ids, bool $returnJustContentDTO = true): array
    {
        $yt = new self();
        if (!$ids) return [];
        $videos = [];
        $videos = $yt->client->videos->listVideos(['snippet','contentDetails'], [
            'id' => $ids
        ]);

        return array_map(function ($video) use ($returnJustContentDTO) {
            $kind = ($video->snippet->liveBroadcastContent == 'live') ? Kind::Stream : Kind::Video;

            $resultDTO = new ResultDTO(Platform::YouTube, $kind);
            $contentDTO = new ContentDTO(
                Platform::YouTube,
                $kind,
                $video->id
            );


            $contentDTO->creator_id = $video->snippet->channelId;
            $contentDTO->name = $video->snippet->title;
            $contentDTO->description = $video->snippet->description;
            $contentDTO->duration = Tools::convertYouTubeDurationToSeconds($video->contentDetails->duration);
            $contentDTO->tags = $video->snippet->tags ?? [];
            $contentDTO->publish_time = Carbon::parse($video->snippet->publishedAt);
            $contentDTO->thumbnail_url = $video->snippet->thumbnails->medium->url;
            $categoryDTO = new ContentDTO(Platform::YouTube, Kind::Category, $video->snippet->categoryId);
            $contentDTO->category = $categoryDTO;
            $contentDTO->language = $video->snippet->defaultLanguage;

            if($kind == Kind::Stream){
                $contentDTO->is_live = true;
            }

            $resultDTO->content = $contentDTO;

            if ($returnJustContentDTO) return $contentDTO;

            $resultDTO->kind = $kind;
            $resultDTO->creator = new CreatorDTO(Platform::YouTube, $video->snippet->channelId);
            return $resultDTO;
        },$videos->getItems());
    }




    public static function getLogInUrl(array $scopes = null, string $redirect_url_path = null){
        //check if user already has linked their account
        $creator = auth()->user()->creator()->with('sources')->first();
        if(!$creator){
            abort(403, 'You must be logged in to link your YouTube account');
        }
        if(!$creator->sources->contains('source_name', Platform::YouTube->value)){
            return (new Google($scopes, $redirect_url_path))->client->createAuthUrl();
        }
        else{
            abort(403, 'You have already claimed a YouTube channel');
        }
    }

    public static function getRefreshAccessToken($refreshToken): array
    {
        $google = new Google();
        $google->client->refreshToken($refreshToken);
        $access_token = $google->client->getAccessToken();

        return [
            'access_token' => $access_token['access_token'],
            'refresh_token' => $access_token['refresh_token'],
            'expires_in' => $access_token['expires_in'],
        ];
    }

    private static function extractCreatorToDTO(\Google\Service\YouTube\Channel $data): CreatorDTO
    {
        $creatorDTO = new CreatorDTO(Platform::YouTube, $data->id);
        $creatorDTO->name = $data->snippet->title;
        $creatorDTO->avatar_url = $data->snippet->thumbnails->default->url;
        $creatorDTO->banner_url = $data->brandingSettings->image ? $data->brandingSettings->image->bannerExternalUrl . '=w2120-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj' : null;
        $creatorDTO->description = $data->snippet->description;
        $creatorDTO->region = $data->snippet->country ?? null;
        $creatorDTO->language = $data->snippet->defaultLanguage ?? null;
        return $creatorDTO;
    }


}
