<?php

namespace App\Http\Controllers\ApiControllers;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\AuthYouTube;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Models\CreatorModels\CreatorSource;
use DailymotionAuthRequiredException;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class LinkingApiController extends Controller
{

    public function myCreatorSources()
    {
        $sources = [];
        auth()->user()->creator()->with('sources')->first()->sources()->get(['source_name', 'external_channel_id'])->each(
            function ($source) use (&$sources){
                $sources[$source->source_name] = $source->external_channel_id;
            }
        );

        return ["sources" => $sources];
    }


    public function link(string $platform)
    {
        $code = request('code');

        switch ($platform) {
            case Platform::YouTube->value:

                try {
                $yt = new AuthYouTube(AuthYouTube::getAccessTokenWithCode($code));
                $yt_channel_id = $yt->getMyCreator()->id;

                self::breakIfChannelClaimed($yt_channel_id, Platform::YouTube);

                CreatorSource::create([
                    'source_name' => Platform::YouTube->value,
                    'external_channel_id' => $yt_channel_id,
                    'creator_id' => Auth::user()->creator->id,
                    'access_token' => $yt->client->getClient()->getAccessToken()['access_token'],
                    'refresh_token' => $yt->client->getClient()->getAccessToken()['refresh_token'],
                ]);

                return response()->json(['message' => 'success']);
                } catch (Exception $e) {
                    if($e->getCode() == 403) {
                        abort('403', $e->getMessage());
                    }
                    abort('401',$e->getMessage());
                }

            case Platform::Dailymotion->value:
                try {

                    $api = new \Dailymotion();

                    $api->setGrantType(
                        \Dailymotion::GRANT_TYPE_AUTHORIZATION,
                        config('platforms.dailymotion.client_key'),
                        config('platforms.dailymotion.client_secret'),
                        ['email','userinfo','manage_videos','manage_playlists','manage_subscriptions','manage_likes'],
                        ['redirect_uri'=>Tools::convertRedirectPathToUrl(config('platforms.dailymotion.redirect_url'))]
                    );
//                    $dm = (new Dailymotion(true));
//                    dd($dm->client->getSession());
//                    dd($dm);
                    try
                    {
                        dd($api->getAccessToken());
                    }
                    catch (DailymotionAuthRequiredException $e)
                    {
                        return redirect($api->getAuthorizationUrl());
                    }
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
                } catch (Exception $e) {
                    abort('401');
                }
            case Platform::Vimeo->value:
                try {

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
                } catch (Exception $e) {
                    abort('401');
                }
            case Platform::Twitch->value:
                try {
                    $twitch_oauth = resolve(Twitch::class)->client->getOauthApi();
                    $tokens = json_decode($twitch_oauth->getUserAccessToken($code,convertRedirectPathToUrl(strval(config('platforms.twitch.redirect_url'))))->getBody()->getContents());


                    $twitch = new Twitch($tokens->access_token);
                    $twitch_channel_id = $twitch->getMyCreator()->id;

                    self::breakIfChannelClaimed($twitch_channel_id, Platform::Twitch);

                    CreatorSource::create([
                        'source_name' => Platform::Twitch->value,
                        'external_channel_id' => $twitch_channel_id,
                        'creator_id' => Auth::user()->creator->id,
                        'access_token' => $tokens->access_token,
                        'refresh_token' => $tokens->refresh_token
                    ]);

                    return redirect()->route('studio.dashboard');
                } catch (Exception $e) {
                    abort('401');
                }

            default:
                abort(400);
        }
    }

    private static function breakIfChannelClaimed(string $externalChannelID, Platform $source)
    {
        $source = CreatorSource::where('external_channel_id', $externalChannelID)
            ->where('source_name', $source->name)->first();

        if(!$source) return;
        $user = $source->creator->user();

        if( $user->exists()) {
            if($user->first()->id == auth()->user()->id){
                throw new Exception("You have already claimed this channel", 403);
            }
            throw new Exception('That channel has already been claimed by another user', 403);
        }
    }




    public function logIn(string $platform)
    {
        $platform = Platform::fromValue($platform)->getPlatformAuthClass();
        // if $platform has a logIn method, call it
        if (method_exists($platform, 'getLoginUrl')) {
            $auth_url = $platform::getLoginUrl();
        }
        else {
            abort(400);
        }

        if (!isset($auth_url)) abort(400);

        return ['url' => $auth_url];
    }
}
