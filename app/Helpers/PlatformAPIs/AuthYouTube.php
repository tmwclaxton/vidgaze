<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Audience;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanUpload;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanLogin;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\Tools;
use App\Helpers\UploadDTO;
use Carbon\Carbon;
use Google\Client;
use Google\Service\YouTube\ThumbnailDetails;
use Google_Service_YouTube;
use Laravel\Octane\Facades\Octane;

class AuthYouTube extends YouTube implements iCanLogin, iCanUpload
{

    public function __construct($access_token)
    {
        $google = new Google();
        $google->client->setAccessToken($access_token);
        $this->google_client = $google->client;
        $this->client = new Google_Service_YouTube($google->client);
    }

    public static function getAccessTokenWithCode(string $code, array $scopes = null, string $redirect_url_path = null): array{
        $google = new Google($scopes, $redirect_url_path);
        $accessToken = $google->client->fetchAccessTokenWithAuthCode($code);
        return $accessToken;
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

    public function getMyCreator(): CreatorDTO
    {
        $data = $this->client->channels->listChannels(['snippet', 'brandingSettings'], [
            'mine' => true,
        ])->getItems()[0];
        return parent::extractCreatorToDTO($data);
    }

    public function upload(UploadDTO $uploadDTO): string{
        $video_storage_path = storage_path('app/'.$uploadDTO->video_path);

        $snippet = new \Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($uploadDTO->title);
        $snippet->setDescription($uploadDTO->description);
        $snippet->setTags($uploadDTO->tags);
        $snippet->setCategoryId($uploadDTO->category->youtube_category_id);

        $status = new \Google_Service_YouTube_VideoStatus();
        $status->setEmbeddable(true);
        $status->setPrivacyStatus($uploadDTO->visibility->value);
        $status->setSelfDeclaredMadeForKids($uploadDTO->audience == Audience::KIDS);

//        $ageGating = new \Google_Service_YouTube_VideoAgeGating();
//        $ageGating->setRestricted($uploadDTO->audience == Audience::MATURE);


        $video = new \Google_Service_YouTube_Video();
        $video->setStatus($status);
        $video->setSnippet($snippet);
//        $video->setAgeGating($ageGating);


        $chunkSizeBytes = 1 * 1024 * 1024;
        $this->google_client->setDefer(true);

        $insertRequest = $this->client->videos->insert(
            ["status", "snippet"/*, "ageGating"*/],
            $video,
        );

        $media = new \Google_Http_MediaFileUpload(
            $this->google_client,
            $insertRequest,
            'video/*',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($video_storage_path));

        $status = false;
        $handle = fopen($video_storage_path, "rb");
        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }
        fclose($handle);
        $this->google_client->setDefer(false);
        $this->setThumbnail($status['id'], $uploadDTO->thumbnail_path);
        return $status['id'];
    }

    public function setThumbnail(string $video_id, string $thumbnail_path){
        return $this->client->thumbnails->set(
            $video_id,
            array(
                'data' => \Storage::get('public/'.$thumbnail_path),
                'mimeType' => 'application/octet-stream',
                'uploadType' => 'multipart'
            )
        );
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
}
