<?php

namespace App\Http\Controllers\Tools;

use App\Enums\Platform;
use App\Helpers\OAuth\LogInWithDailymotion;
use App\Helpers\OAuth\LogInWithTwitch;
use App\Helpers\OAuth\LogInWithVimeo;
use App\Helpers\OAuth\LogInWithYouTube;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Google;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\YouTube;
use App\Http\Controllers\Controller;
use App\Models\CreatorModels\CreatorSource;
use Google_Service_YouTube;
use Illuminate\Support\Facades\Auth;

class LinkingController extends Controller
{
    public function link(string $platform)
    {
        $code = request('code');

        switch ($platform) {
            case Platform::YouTube->value:
                try {
                    $client = resolve(Google::class)->client;
                    $client->fetchAccessTokenWithAuthCode($code);
//                    $access_token = $client->getAccessToken();
//                    dd($access_token);
                    //$client->setAccessToken($access_token);

                    // Define service object for making API requests.
                    $youtube = new Google_Service_YouTube($client);
                    $queryParams = [
                        'mine' => true
                    ];
                    $response = $youtube->channels->listChannels('id', $queryParams);

                    ddd($response);
                    self::breakIfChannelClaimed($response[0]['id'], Platform::YouTube);

                    $source = new CreatorSource();
                    $source->source_name = Platform::YouTube->value;
                    $source->external_channel_id = $response[0]['id'];
                    $source->creator_id = Auth::user()->creator->id;
                    $source->save();

                    return redirect()->route('studio');
                } catch (\Exception $e) {
                    return abort('401');
                }

            case Platform::Dailymotion->value:
                try {
                    $dailymotion_client = (new Dailymotion(true))->client;
                    $dailymotion_client->getAccessToken();

                    $response = $dailymotion_client->get('/me', array('fields' => array('id')));

                    self::breakIfChannelClaimed($response['id'], Platform::Dailymotion);

                    $source = new CreatorSource();
                    $source->source_name = Platform::Dailymotion->value;
                    $source->external_channel_id = $response['id'];
                    $source->creator_id = Auth::user()->creator->id;
                    $source->save();

                    return redirect()->route('studio');
                } catch (\Exception $e) {
                    return abort('401');
                }
            case Platform::Vimeo->value:
                try {
                    $vimeo = resolve(\App\Helpers\PlatformAPIs\Vimeo::class)->client;
                    $token = $vimeo->accessToken($code, convertRedirectPathToUrl(config('platforms.vimeo.redirect_url')));
                    $vimeo->setToken($token['body']['access_token']);

                    $me = $vimeo->request('/me');
                    $id = str_replace("/users/", "", $me["body"]["uri"]);

                    self::breakIfChannelClaimed($id, Platform::Vimeo);

                    $source = new CreatorSource();
                    $source->source_name = Platform::Vimeo->value;
                    $source->external_channel_id = $id;
                    $source->creator_id = Auth::user()->creator->id;
                    $source->save();

                    return redirect()->route('studio');
                } catch (\Exception $e) {
                    return abort('401');
                }
            case Platform::Twitch->value:
                try {
                    $twitch_oauth = resolve(Twitch::class)->client->getOauthApi();
                    $token = $twitch_oauth->getUserAccessToken($code,convertRedirectPathToUrl(strval(config('platforms.twitch.redirect_url'))));

                    $data = json_decode($token->getBody()->getContents());
                    // Your bearer token
                    $twitch_access_token = $data->access_token ?? null;

                    // Make the API call. A ResponseInterface object is returned.
                    $response = resolve(Twitch::class)->client->getUsersApi()->getUserByAccessToken($twitch_access_token);
                    // Get and decode the actual content sent by Twitch.
                    $responseContent = json_decode($response->getBody()->getContents(),true);
                    $id = $responseContent["data"][0]["id"];

                    self::breakIfChannelClaimed($id, Platform::Twitch);

                    $source = new CreatorSource();
                    $source->source_name = Platform::Twitch->value;
                    $source->external_channel_id = $id;
                    $source->creator_id = Auth::user()->creator->id;
                    $source->save();

                    return redirect()->route('studio');
                } catch (\Exception $e) {
                    return abort('401');
                }

            default:
                return abort(400);
        }
    }

    public function logIn(string $platform)
    {
        $platform = Platform::fromValue($platform)->getPlatformClass();
        // if $platform has a logIn method, call it
        if (method_exists($platform, 'getLoginUrl')) {
            $auth_url = $platform::getLoginUrl();
        }
        else {
            return abort(400);
        }

        if (!isset($auth_url)) abort(400);

        return redirect($auth_url);
    }

    private static function breakIfChannelClaimed(string $externalChannelID, Platform $source)
    {
        if( (bool)(CreatorSource::where('external_channel_id', '=', $externalChannelID)
            ->where('source_name', '=', $source->name)->first()))
        {
            return abort(403,"That channel has already been claimed by another user");
        }
    }
}
