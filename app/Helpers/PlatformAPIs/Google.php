<?php

namespace App\Helpers\PlatformAPIs;

use App\Helpers\Tools;
use Google\Client;
use Google_Service_YouTube;

class Google
{
    public $client;

    public function __construct(array $scopes = null, string $redirect_url_path = null)
    {
        $client = new Client();
        $client->setDeveloperKey(config('platforms.google.developer_key'));
        $client->setClientSecret(config('platforms.google.client_secret'));
        $client->setClientId(config('platforms.google.client_id'));

        isset($scopes) ?  $client->addScope($scopes) : $client->addScope(GOOGLE_SERVICE_YOUTUBE::YOUTUBE_FORCE_SSL);

        isset($redirect_url_path) ? $client->setRedirectUri(Tools::convertRedirectPathToUrl($redirect_url_path)) :  $client->setRedirectUri(Tools::convertRedirectPathToUrl(config('platforms.google.redirect_url.link')));

        // offline access will give you both an access and refresh token so that
        // your app can refresh the access token without user interaction.
        $client->setAccessType('offline');
        // Using "consent" ensures that your application always receives a refresh token.
        // If you are not using offline access, you can omit this.
//            $client->setApprovalPrompt('consent');
        $client->setIncludeGrantedScopes(true);   // incremental auth

        $this->client = $client;
    }
}
