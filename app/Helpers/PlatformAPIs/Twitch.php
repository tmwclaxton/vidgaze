<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\PlatformAPIs\PlatformInterfaces\isValidatable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use TwitchApi\HelixGuzzleClient;
use TwitchApi\TwitchApi;
use App\Models\CreatorModels\TwitchLogin;

class Twitch implements iSearchable, iIsPlatform, isValidatable
{
    public TwitchApi $client;

    public function __construct($access_token = null)
    {
        if(isset($access_token)) $this->access_token = $access_token;
        $helixGuzzleClient = new HelixGuzzleClient(config('platforms.twitch.client_id'));
        $this->client = new TwitchApi($helixGuzzleClient, config('platforms.twitch.client_id'), config('platforms.twitch.client_secret'));
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


    // also deletes ghost creator streams if they are not live
    public static function updateStreamerStatus(array $broadcaster_ids = null): void
    {
        if(!$broadcaster_ids){
            foreach (TwitchLogin::oldest()->take(100)->get() as $login){
                $broadcaster_ids[] = $login->twitch_source_id;
                $login->touch();
            }
        }
        if (!$broadcaster_ids) return;
        $twitch = new Twitch();

        $url_params = implode("&user_id=", $broadcaster_ids);

        $response = $twitch->client->getStreamsApi()->getStreamForUserId($twitch->getAppBearerToken(), $url_params);

        $data = json_decode($response->getBody()->getContents(), true)['data'];
        $live = Arr::map($data, function ($item){
            return $item['user_id'];
        });

        $not_live = array_diff($broadcaster_ids, $live);

        // for each not live stream where has no ->creator()->user() relation, delete stream

        if($not_live) TwitchLogin::whereIn('twitch_source_id', $not_live)->get()
            ->map(function($item) {
                if(!$item->source()->creator()->first()->user()->first()) $item->source()->creator()->first()->streams()->delete();

                $item->source()->creator()->first()->update(['is_live' => 0]);
            });
        if($live) TwitchLogin::whereIn('twitch_source_id', $live)->get()
            ->map(function($item) {
                $item->source()->creator()->first()->update(['is_live' => 1]);
            });
    }

    public static function getCategories(array $ids = null, $topTwenty = false) : array //ContentDTO
    {
        $t = new Twitch();

        $response = $topTwenty ? json_decode($t->client->getGamesApi()->getTopGames($t->getAppBearerToken())->getBody()->getContents())->data
            : json_decode($t->client->getGamesApi()->getGames($t->getAppBearerToken(), $ids)->getBody()->getContents())->data;

        return array_map(function ($value){
            $contentDTO = new ContentDTO(Platform::Twitch, Kind::Category, $value->id);
            $contentDTO->name = $value->name;
            $contentDTO->category_slug = convertNameToSlug($value->name);
            $contentDTO->thumbnail_url = str_replace('{width}x{height}','564x750', $value->box_art_url);
            return $contentDTO;
        }, $response);
    }

    public static function getTopStreamsByCategory(string $category_id){
        $t = new Twitch();

        $response = json_decode($t->client->getStreamsApi()->getStreamsByGameId($t->getAppBearerToken(), $category_id)->getBody()->getContents())->data;

        return array_map(function ($value){
            $resultDTO = new ResultDTO(Platform::Twitch, Kind::Stream);

            $categoryDTO = new ContentDTO(Platform::Twitch, Kind::Category, $value->game_id);
            $categoryDTO->name = $value->game_name;
            $categoryDTO->category_slug = convertNameToSlug($value->game_name);

            $creatorDTO = new CreatorDTO(Platform::Twitch, $value->user_id);
            $creatorDTO->twitch_login = $value->user_login;
            $creatorDTO->is_live = true;
            $creatorDTO->name = $value->user_name;

            $contentDTO = new ContentDTO(Platform::Twitch, Kind::Stream, $value->id);
            $contentDTO->creator_id = $value->user_id;
            $contentDTO->name = $value->title;
            $contentDTO->views = $value->viewer_count;
            $contentDTO->publish_time = Carbon::make($value->started_at);
            $contentDTO->language = $value->language;
            $contentDTO->is_live = true;
            $contentDTO->thumbnail_url = str_replace('{width}x{height}','1920x1080', $value->thumbnail_url);
            if ($value->tags === null) {
                $contentDTO->tags = [];
            } else {
                $contentDTO->tags = $value->tags;
            }
            $contentDTO->category_id = $value->game_id;

            $contentDTO->category = $categoryDTO;

            $resultDTO->creator = $creatorDTO;
            $resultDTO->content = $contentDTO;
            return $resultDTO;
        }, $response);
    }



    // do not exceed 20 categories or 20 streams
    public static function updateTopCategories(int $maxCategories = 20, $maxStreamsPerCategory = 20){
        $categories = array_slice(Twitch::getCategories(null, true), 0, $maxCategories);
        $streams = [];
        foreach ($categories as $category){
            $streams[] =
                ResultDTO::saveAll(
                    array_slice(
                        Twitch::getTopStreamsByCategory($category->id),
                        0, $maxStreamsPerCategory
                    )
                );
        }
        return $streams;
    }


    public static function validate(array $results): array
    {
        // TODO: Implement validate() method.
    }
}
