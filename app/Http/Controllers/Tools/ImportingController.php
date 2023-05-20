<?php

namespace App\Http\Controllers\Tools;

use App\Helpers\OAuth\LogInWithYouTube;
use App\Helpers\PlatformAPIs\YouTube;
use App\Helpers\SearchResultDTO;
use App\Http\Controllers\Controller;
use App\Models\CreatorModels\Subscription;

class ImportingController extends Controller
{
    public function index()
    {
        return view('/auth/import_subscriptions');
    }

    public function import(string $platform){
        $code = request('code');

        $youtube = new YouTube($code, ['https://www.googleapis.com/auth/youtube.readonly'],  config('platforms.google.redirect_url.import'));

        $queryParams = [
            'maxResults' => 50,
            'mine' => true,
            'pageToken' => null,
        ];
        $response = $youtube->client->subscriptions->listSubscriptions('snippet', $queryParams);

        $response_subscriptions = $response->getItems();
        while ($response->nextPageToken)
        {
            $queryParams['pageToken'] = $response->nextPageToken;
            $response = $youtube->client->subscriptions->listSubscriptions('snippet', $queryParams);
            $response_subscriptions = array_merge($response_subscriptions, $response->getItems());
        }
        $subscription_ids = array_map(fn($val)=>$val->snippet->resourceId->channelId ,$response_subscriptions);

        $creator_DTOs = YouTube::getChannel($subscription_ids, true);

        $id = auth()->user()->creator()->get()->first()->id;
        foreach ($creator_DTOs as $creator_DTO){
            $creator = SearchResultDTO::createCreatorModelFromResultDTO($creator_DTO);

            Subscription::firstOrCreate([
                'subscriber_id' => $id,
                'creator_id' => $creator->id
            ]);
        }
        dd('success');
    }

    public function logIn(string $platform){
        $scopes =[
            'https://www.googleapis.com/auth/youtube.readonly',
        ];
        $auth_url = LogInWithYouTube::logIn($scopes,  config('platforms.google.redirect_url.import'));
        return redirect($auth_url);
    }
}
