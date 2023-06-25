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
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use App\Http\Controllers\Controller;
use App\Models\CreatorModels\CreatorSource;
use Google_Service_YouTube;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class LinkingController extends Controller
{
    public function link(string $platform)
    {
        $code = request('code');

        switch ($platform) {
            case Platform::YouTube->value:
                try {
                    $yt = new YouTube($code);
                    $yt_channel_id = $yt->getMyCreator()->id;

                    self::breakIfChannelClaimed($yt_channel_id, Platform::YouTube);

                    CreatorSource::create([
                        'source_name' => Platform::YouTube->value,
                        'external_channel_id' => $yt_channel_id,
                        'creator_id' => Auth::user()->creator->id,
                        'access_token' => $yt->client->getClient()->getAccessToken()['access_token'],
                        'refresh_token' => $yt->client->getClient()->getAccessToken()['refresh_token'],
                    ]);

                    return redirect()->route('studio.dashboard');
                } catch (\Exception $e) {
                    if($e->getCode() == 403) {
                        return abort('403', $e->getMessage());
                    }
                    return abort('401',$e->getMessage());
                }

            case Platform::Dailymotion->value:
//                try {
                    $dailymotion_client = (new Dailymotion(true))->client;
//                $dailymotion_client->getAccessToken();


                $client = new Client();

                $tokenEndpoint = 'https://api.dailymotion.com/oauth/token';
                $clientID = config('platforms.dailymotion.client_key');
                $clientSecret = config('platforms.dailymotion.client_secret');
                $redirectURI = config('platforms.dailymotion.redirect_url');
                $authorizationCode = $code;

                $response = $client->post($tokenEndpoint, [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'client_id' => $clientID,
                        'client_secret' => $clientSecret,
                        'redirect_uri' => $redirectURI,
                        'code' => $authorizationCode,
                    ],
                ]);

                $responseData = json_decode($response->getBody(), true);
                dd($responseData);
                $accessToken = $responseData['access_token'];
                $expiresIn = $responseData['expires_in'];
                $refreshToken = $responseData['refresh_token'];



                $dailymotion_client->getAccessToken();
                    $response = $dailymotion_client->get('/me', array('fields' => array('id')));

                    self::breakIfChannelClaimed($response['id'], Platform::Dailymotion);

                    dd($response);
                    $source = new CreatorSource();
                    $source->source_name = Platform::Dailymotion->value;
                    $source->external_channel_id = $response['id'];
                    $source->creator_id = Auth::user()->creator->id;
                    $source->save();


                    return redirect()->route('studio.dashboard');
//                } catch (\Exception $e) {
//                    return abort('401');
//                }
            case Platform::Vimeo->value:
//                try {

                    $vimeo = (new Vimeo($code));

                    $vm_channel_id = $vimeo->getMyCreator()->id;

                    self::breakIfChannelClaimed($vm_channel_id, Platform::Vimeo);

                    CreatorSource::create([
                        'source_name' => Platform::Vimeo->value,
                        'external_channel_id' => $vm_channel_id,
                        'creator_id' => Auth::user()->creator->id,
                        'access_token' => $vimeo->access_token,
                        'refresh_token' => null,
                    ]);

                return redirect()->route('studio.dashboard');
//                } catch (\Exception $e) {
//                    return abort('401');
//                }
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

                    return redirect()->route('studio.dashboard');
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
        $source = CreatorSource::where('external_channel_id', $externalChannelID)
            ->where('source_name', $source->name)->first();

        if(!$source) return;
        $user = $source->creator->user();

        if( $user->exists()) {
            if($user->first()->id == auth()->user()->id){
                throw new \Exception("You have already claimed this channel", 403);
            }
            throw new \Exception('That channel has already been claimed by another user', 403);
        }
    }
}
