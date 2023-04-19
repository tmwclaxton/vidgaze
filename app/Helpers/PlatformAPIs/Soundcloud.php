<?php

namespace App\Helpers\PlatformAPIs;

use GuzzleHttp\Client;

class Soundcloud extends aPlatformAPI implements iPlatfromSearch
{

    public Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.soundcloud.com',
            'timeout'  => 2.0,
        ]);
    }

    public static function search($searchQuery, int $maxResults = 20, $pageToken = null, $filters = null)
    {
        $sc = new Soundcloud();
        $response = $sc->client->request('GET', '/tracks', [
            'query' => [
                'client_id' => 'YOUR_CLIENT_ID',
                'q' => 'Electronic',
            ],
        ]);
    }
}
