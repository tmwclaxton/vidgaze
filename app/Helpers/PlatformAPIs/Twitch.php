<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platforms;
use App\Helpers\SearchResultDTO;
use App\Models\Creator;
use App\Models\CreatorSource;
use App\Models\TwitchLogin;
use Carbon\Carbon;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Goutte\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use TwitchApi\HelixGuzzleClient;
use function Deployer\get;

class Twitch extends aPlatformAPI
{
    public $client;

    public function __construct()
    {
        $helixGuzzleClient = new HelixGuzzleClient(config('platforms.twitch.client_id'));
        $this->client = new \TwitchApi\TwitchApi($helixGuzzleClient, config('platforms.twitch.client_id'), config('platforms.twitch.client_secret'));
    }

    public function getAppBearerToken(){
        $token = $this->client->getOauthApi()->getAppAccessToken();
        $data = json_decode($token->getBody()->getContents());

        // Your bearer token
        return $data->access_token ?? null;
    }

//    public static function isLive(string $streamerId){
//
//        $host = convertRedirectPathToUrl(); // this is the default
//        $capabilities = DesiredCapabilities::chrome();
//        $driver = RemoteWebDriver::create($host, $capabilities, 5000);
//
//        echo $driver->executeScript('return "hello"');
//        $driver->quit();
//
//    }


    /**
     * Checks if streamer is live and updates the creator is_live value
     * If no ids are passed then automatically pulls the 100 oldest creators from the table
     * @param array|null $broadcaster_ids
     * @return void
     * @throws GuzzleException
     */
    public static function updateStreamerStatus(array $broadcaster_ids = null): void
    {
        if(!$broadcaster_ids){
            foreach (TwitchLogin::oldest()->take(100)->get() as $login){
                $broadcaster_ids[] = $login->twitch_source_id;
                $login->touch(); //silly josh
            }
        }
        $twitch = new Twitch();

        $url_params = implode("&user_id=", $broadcaster_ids);

        $response = $twitch->client->getStreamsApi()->getStreamForUserId($twitch->getAppBearerToken(), $url_params);

        $data = json_decode($response->getBody()->getContents(), true)['data'];
        $live = Arr::map($data, function ($item){
            return $item['user_id'];
        });

        $not_live = array_diff($broadcaster_ids, $live);

        if($not_live) TwitchLogin::whereIn('twitch_source_id', $not_live)->get()
            ->map(function($item) {
                $item->source->creator()->first()->update(['is_live' => 0]);
            });
        if($live) TwitchLogin::whereIn('twitch_source_id', $live)->get()
            ->map(function($item) {
                $item->source->creator()->first()->update(['is_live' => 1]);
            });



//        dd(json_decode($response->getBody()->getContents(), true));

//        $guz = new \GuzzleHttp\Client();
////        dd('https://api.twitch.tv/helix/streams/?'.http_build_query([
////            'user_id' => '36511475',
////            'user_id' => '631142655'
////        ]));
//        $url = 'https://api.twitch.tv/helix/streams?user_id=52091823&user_id=7601562';
////        $guz->request('GET', $url, ['auth' => ['username', 'password']]);
//
//        $r = $guz->get($url, [
//            'headers' => [
//                'Authorization' => 'Bearer '.$twitch->getAppBearerToken(),
//                'Client-Id' => config('platforms.twitch.client_id')
//            ]
//        ]);
////        dd($r->getBody());
//        dd(json_decode($r->getBody()->getContents(), true));
    }

    public static function updateTopCategories(){
        $categories = Twitch::getCategories(null, true);
        foreach ($categories as $category){
            foreach (Twitch::getTopStreamsByCategory($category->category_id) as $stream){
                SearchResultDTO::createStreamModelFromResultDTO($stream);
            }
        }
    }

    public static function isStreamLive(){

        // Create a new Goutte client
        $client = new Client();

        // Retrieve the HTML source of the web page containing the stream
        $crawler = $client->request('GET', 'https://www.twitch.tv/jukeyz');
//        $playerType = $crawler->filter('div.root')->attr('id');
//        dd($playerType);
        dd($crawler);

        $playerType = $crawler->filter('div.Layout-sc-1xcs6mc-0.video-player')->attr('data-a-player-type');

        dd($playerType);
    }

    //results will be sorted by most popular but will always include the channel that perfectly matches the query
    //so ideally maxResults >= 2
    public static function search($searchQuery, int $maxResults = 2, $pageToken = null, $filters = null)
    {
        $twitch = new Twitch();
        $twitch_access_token = $twitch->getAppBearerToken();
        $p_start = microtime(true);

        try {
            $response = json_decode($twitch->client->getSearchApi()->searchChannels($twitch_access_token, $searchQuery, null, $maxResults)->getBody()->getContents());
        } catch (GuzzleException $e) {
            return [
                "pageTokenInfo" => null,
                "results" => []
            ];
        }

        $results = array_map(function ($value) use ($twitch){
            $result = new SearchResultDTO();

            // filter banned accounts
            try {
                $result->channel_id = $twitch->getChannelByUsername($value->broadcaster_login)->id;
            }
            catch (\Exception $e){
                return null;
            }

            $result->kind = Kind::Creator;

            $result->channel_name = $value->display_name;
            $result->platform = Platforms::Twitch;
            $result->avatar_url = $value->thumbnail_url;
            $result->language = $value->broadcaster_language;
            $result->bio = $value->title;
            $result->is_live = $value->is_live;
            $result->twitch_login = $value->broadcaster_login;

            $result->category_id = $value->game_id;

            return  $result;
        }, (array) $response->data);

        // remove null values (ie banned accounts)
        $results = array_filter($results, function($value) {
            return $value !== null;
        });
        return [
            "pageTokenInfo" => null,
            "results" => $results
        ];
    }



    /**
     * @param string $id
     * @return SearchResultDTO|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * If the channel is not live it will return null
     */
    public static function getChannelStream(string $id): null | SearchResultDTO
    {
        $t = new Twitch();
        $api = $t->client->getStreamsApi();
        $twitch_access_token = $t->getAppBearerToken();

//        dd($api->getStreamForUserId($twitch_access_token, $id)->getHeaders()['Ratelimit-Remaining']);
        $data = json_decode($api->getStreamForUserId($twitch_access_token, $id)->getBody()->getContents())->data;

        if(!isset($data[0])){
            return null;
        }

        $DTO = new SearchResultDTO();
        $DTO->platform = Platforms::Twitch;
        $DTO->stream_id = $data[0]->id;
        $DTO->twitch_login = $data[0]->user_login;
        $DTO->stream_name = $data[0]->title;
        $DTO->views = $data[0]->viewer_count;
        $DTO->publish_time = Carbon::make($data[0]->started_at);
        $DTO->language = $data[0]->language;
        $DTO->views = $data[0]->viewer_count;
        $DTO->is_live = true;
        $DTO->category_id = $data[0]->game_id;
        $DTO->category_name = $data[0]->game_name;
        $DTO->category_slug = convertNameToSlug($data[0]->game_name);
        $DTO->thumbnail_url = str_replace('{width}x{height}','1920x1080', $data[0]->thumbnail_url);

        return $DTO;
    }

    /**
     * @param string|array $username
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * input a string and get one result
     * input an array of usernames and get an array of results
     */
    public function getChannelByUsername(string|array $username)
    {
        $query = $username;
        if(is_array($username)){
            $query = implode('&login=', $username);
            return json_decode($this->client->getUsersApi()->getUserByUsername($this->getAppBearerToken(), $query)->getBody()->getContents())->data;
        }
        return json_decode($this->client->getUsersApi()->getUserByUsername($this->getAppBearerToken(), $query)->getBody()->getContents())->data[0];
//        return json_decode($this->client->getUsersApi()->getUserByUsername($this->getAppBearerToken(), $query)->getBody()->getContents())->data[0];
    }


    public static function getCategories(string | array $id = null, $topTwenty = false) : array //SearchResultDTO
    {
        $t = new Twitch();
        if(!$topTwenty && !is_array($id)){$id = [$id];}

        $response = $topTwenty? json_decode($t->client->getGamesApi()->getTopGames($t->getAppBearerToken())->getBody()->getContents())->data
            : json_decode($t->client->getGamesApi()->getGames($t->getAppBearerToken(), $id)->getBody()->getContents())->data;

        $results = array_map(function ($value){

            $DTO = new SearchResultDTO();
            $DTO->category_id = $value->id;
            $DTO->kind = Kind::Category;
            $DTO->platform = Platforms::Twitch;
            $DTO->category_name = $value->name;
            $DTO->assignable = true;
            $DTO->category_slug = convertNameToSlug($value->name);
            $DTO->category_thumbnail_url = str_replace('{width}x{height}','564x750', $value->box_art_url);
            return $DTO;
        }, $response);
        return $results;
    }

    public static function getTopStreamsByCategory(string | array $id){
        $t = new Twitch();

//        $query = $id;
//        if(is_array($id)) {
//            $query = implode('&id=', $id);
//        }
        $response = json_decode($t->client->getStreamsApi()->getStreamsByGameId($t->getAppBearerToken(), $id)->getBody()->getContents())->data;

//        dd($response);
       // dd($response);
        return array_map(function ($value){
            $DTO = new SearchResultDTO();
            $DTO->channel_id = $value->user_id;
            $DTO->platform = Platforms::Twitch;
            $DTO->stream_id = $value->id;
            $DTO->twitch_login = $value->user_login;
            $DTO->stream_name = $value->title;
            $DTO->views = $value->viewer_count;
            $DTO->publish_time = Carbon::make($value->started_at);
            $DTO->language = $value->language;
            $DTO->views = $value->viewer_count;
            $DTO->is_live = true;
            $DTO->category_id = $value->game_id;
            $DTO->category_name = $value->game_name;
            $DTO->category_slug = convertNameToSlug($value->game_name);
            $DTO->thumbnail_url = str_replace('{width}x{height}','1920x1080', $value->thumbnail_url);
            return $DTO;
        }, $response);
    }




 /**
 * @param string|array $id
 * @return mixed
 * @throws \GuzzleHttp\Exception\GuzzleException
 *
 * input a string and get one result
 * input an array of id's and get an array of results
 */
    public static function getChannel(string|array $id): mixed
    {
        $t = new Twitch();
        $api = $t->client->getUsersApi();
        $query = $id;
        if(is_array($id)){
            $query = implode('&login=', $id);
            return json_decode($api->getUserById($t->getAppBearerToken(), $query)->getBody()->getContents())->data;
        }
        return json_decode($api->getUserById($t->getAppBearerToken(), $query)->getBody()->getContents())->data[0];
    }

    public static function makeCreatorModel(string $id, $is_live = false): \Illuminate\Database\Eloquent\Model|Creator
    {
        $response = (new Twitch())->getChannel($id);

        $var = CreatorSource::where('source_name', '=', Platforms::Twitch->name)
            ->where('external_channel_id', '=', $id)
            ->firstOr(function () use ($response, $id, $is_live){
                $creator = Creator::create([
                    'slug' => Platforms::Twitch->getPrefix().'_'.$id,
                    'name' => $response->display_name,
                    'avatar_url' => $response->profile_image_url,
                    'bio' => json_encode($response->description),
                    'is_live' => $is_live

//                    'region' => $result->region,
                    //'language' => $result->language,
                ]);
                $source = CreatorSource::create([
                    'source_name' => Platforms::Twitch->name,
                    'external_channel_id' => $id,
                    'creator_id' => $creator->id,
                ]);
                TwitchLogin::create([
                    'twitch_source_id' => $source->external_channel_id,
                    'twitch_channel_login' => $response->login
                ]);
                return $creator;
            });
        return $var instanceof Creator? $var : $var->creator;

//        $creator = Creator::firstOrNew([
//            'slug' => Platforms::Twitch->getPrefix().'_'.$id,
//        ],[
//            'name' => $response->display_name,
//            'avatar_url' => $response->profile_image_url,
//            'bio' => json_encode($response->description),
////            'region' => $response['country'],
////            'language' => $response['language'],
//        ]);
//
//        $source = CreatorSource::firstOrNew([
//            'source_name' => Platforms::Twitch->name,
//            'external_channel_id' => $id,
//        ],[
//            'creator_id' => $creator->id,
//        ]);
//
//        if($source->creator_id == $creator->id){
//            $creator->save();
//            $source->creator_id = $creator->id;
//            $source->save();
//        }
//        return $creator;
    }
}
