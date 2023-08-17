<?php

namespace App\Helpers\PlatformAPIs;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanLogin;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanUpload;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\UploadDTO;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Vimeo\Vimeo as VimeoSDK;


class AuthVimeo extends Vimeo implements iCanLogin, iCanUpload
{

    public $access_token;

    public function __construct($access_token){
        $this->client = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
        $this->client->setToken($access_token);
        $this->access_token = $access_token;
    }
    public static function getAccessTokenWithCode(string $code, array $scopes = null, string $redirect_url_path = null): array {
        $vimeo = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
        $access_token = $vimeo->accessToken($code, convertRedirectPathToUrl(config('platforms.vimeo.redirect_url')))['body']['access_token'];
        return ["access_token" => $access_token];
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

    public function upload(UploadDTO $uploadDTO): string
    {
        // upload to vimeo using tus approach
        $video_storage_path = storage_path('app/'.$uploadDTO->video_path);

        $privacy = match ($uploadDTO->visibility) {
            Visibility::PUBLIC => 'anybody',
            Visibility::UNLISTED, Visibility::PRIVATE => 'nobody',
        };

        $size = filesize($video_storage_path);
        $uri = $this->client->upload($video_storage_path, [
            'name' => $uploadDTO->title,
            'description' => $uploadDTO->description,
            'privacy' => [
                'view' => $privacy,
                'embed' => 'public'
            ],
            'content_rating' => 'safe',
            'embed' => [
                'buttons' =>[
                    'like' => false,
                    'watchlater' => false,
                    'share' => false,
                    'embed' => false,
                ],
                'logos' => [
                    'custom.active' => false,
                    'vimeo' => false,
                    'custom.sticky' => false,
                ],
                'end_screen.type' => 'empty',
            ]
            //'locale' => 'en', //language
        ]);

        $video_id = str_replace("/videos/", "", $uri);

        $this->setTags($video_id, $uploadDTO->tags);

        $this->setThumbnail($video_id, $uploadDTO->thumbnail_path);
        // add category


        return $video_id;
    }

    public function setThumbnail(string $video_id, string $thumbnail_path){
        $thumbnail_uri = $this->client->request('/videos/'.$video_id, [
            'fields' => 'metadata.connections.pictures.uri',
        ])['body']['metadata']['connections']['pictures']['uri'];

        $thumbnail_storage_path = storage_path('app/public/'.$thumbnail_path);

        return $this->client->uploadImage($thumbnail_uri, $thumbnail_storage_path, true);
    }

    public function setTags($video_id, $tags){
        $tags = array_map(function($tag){
            return ['name' => $tag];
        }, $tags);
        return $this->client->request('/videos/' . $video_id . '/tags', $tags, 'PUT');
    }
}













//{"uuid":"f97ccbe4-7732-410d-920c-1e27c2e2ffc6","displayName":"App\\Jobs\\UploadPlatform","job":"Illuminate\\Queue\\CallQueuedHandler@call","maxTries":null,"maxExceptions":null,"failOnTimeout":false,"backoff":null,"timeout":null,"retryUntil":null,"data":{"commandName":"App\\Jobs\\UploadPlatform","command":"O:23:\"App\\Jobs\\UploadPlatform\":5:{s:10:\"creator_id\";i:1;s:8:\"video_id\";i:9;s:9:\"uploadDTO\";O:21:\"App\\Helpers\\UploadDTO\":12:{s:8:\"video_id\";i:9;s:10:\"video_path\";s:51:\"videos\/Gg1vg8bRpaVVVrsm9uWkxPvDW524tAMaYuDjyO0O.mp4\";s:5:\"title\";s:13:\"Vimeo Private\";s:11:\"description\";s:4:\"desc\";s:4:\"tags\";a:3:{i:0;s:4:\"tag1\";i:1;s:4:\"tag2\";i:2;s:4:\"tag3\";}s:8:\"category\";O:19:\"App\\Models\\Category\":30:{s:13:\"\u0000*\u0000connection\";s:5:\"mysql\";s:8:\"\u0000*\u0000table\";s:10:\"categories\";s:13:\"\u0000*\u0000primaryKey\";s:2:\"id\";s:10:\"\u0000*\u0000keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\u0000*\u0000with\";a:0:{}s:12:\"\u0000*\u0000withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\u0000*\u0000perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\u0000*\u0000escapeWhenCastingToString\";b:0;s:13:\"\u0000*\u0000attributes\";a:15:{s:2:\"id\";i:10;s:4:\"slug\";s:13:\"entertainment\";s:4:\"name\";s:13:\"Entertainment\";s:11:\"description\";N;s:13:\"thumbnail_url\";N;s:19:\"youtube_category_id\";s:2:\"24\";s:18:\"twitch_category_id\";N;s:23:\"dailymotion_category_id\";s:3:\"fun\";s:17:\"vimeo_category_id\";N;s:18:\"rumble_category_id\";N;s:18:\"odysee_category_id\";N;s:19:\"podcast_category_id\";N;s:9:\"tags_json\";N;s:10:\"created_at\";s:19:\"2023-08-02 19:59:50\";s:10:\"updated_at\";s:19:\"2023-08-02 19:59:50\";}s:11:\"\u0000*\u0000original\";a:15:{s:2:\"id\";i:10;s:4:\"slug\";s:13:\"entertainment\";s:4:\"name\";s:13:\"Entertainment\";s:11:\"description\";N;s:13:\"thumbnail_url\";N;s:19:\"youtube_category_id\";s:2:\"24\";s:18:\"twitch_category_id\";N;s:23:\"dailymotion_category_id\";s:3:\"fun\";s:17:\"vimeo_category_id\";N;s:18:\"rumble_category_id\";N;s:18:\"odysee_category_id\";N;s:19:\"podcast_category_id\";N;s:9:\"tags_json\";N;s:10:\"created_at\";s:19:\"2023-08-02 19:59:50\";s:10:\"updated_at\";s:19:\"2023-08-02 19:59:50\";}s:10:\"\u0000*\u0000changes\";a:0:{}s:8:\"\u0000*\u0000casts\";a:0:{}s:17:\"\u0000*\u0000classCastCache\";a:0:{}s:21:\"\u0000*\u0000attributeCastCache\";a:0:{}s:13:\"\u0000*\u0000dateFormat\";N;s:10:\"\u0000*\u0000appends\";a:0:{}s:19:\"\u0000*\u0000dispatchesEvents\";a:0:{}s:14:\"\u0000*\u0000observables\";a:0:{}s:12:\"\u0000*\u0000relations\";a:0:{}s:10:\"\u0000*\u0000touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\u0000*\u0000hidden\";a:0:{}s:10:\"\u0000*\u0000visible\";a:0:{}s:11:\"\u0000*\u0000fillable\";a:0:{}s:10:\"\u0000*\u0000guarded\";a:1:{i:0;s:0:\"\";}}s:14:\"thumbnail_path\";s:55:\"thumbnails\/gRQWnb1GlE1K4TneyBnMquiG87oXLeOOBa6xdSPy.png\";s:10:\"creator_id\";s:1:\"1\";s:9:\"platforms\";a:1:{i:0;E:24:\"App\\Enums\\Platform:Vimeo\";}s:8:\"audience\";E:22:\"App\\Enums\\Audience:ALL\";s:10:\"visibility\";E:28:\"App\\Enums\\Visibility:PRIVATE\";s:12:\"publish_time\";N;}s:8:\"platform\";r:78;s:7:\"batchId\";s:36:\"99e9e501-2e63-4bac-92e8-e3130bf2e016\";}"},"telescope_uuid":"99e9e501-39b0-4883-ab08-83cbe5b54d47","id":"f97ccbe4-7732-410d-920c-1e27c2e2ffc6","attempts":0,"type":"job","tags":[],"silenced":false,"pushedAt":"1692298484.927"}
