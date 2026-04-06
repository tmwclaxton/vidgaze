<?php

namespace App\Http\Controllers\ApiControllers;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\AuthTwitch;
use App\Helpers\PlatformAPIs\AuthVimeo;
use App\Helpers\PlatformAPIs\AuthYouTube;
use App\Helpers\Tools;
use App\Http\Controllers\Controller;
use App\Models\CreatorModels\CreatorSource;
use App\Support\PlatformRegistry;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class LinkingApiController extends Controller
{
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
                    if ($e->getCode() == 403) {
                        abort(403, $e->getMessage());
                    }
                    abort(401, $e->getMessage());
                }

            case Platform::Dailymotion->value:
                try {
                    $redirect = Tools::convertRedirectPathToUrl(config('platforms.dailymotion.redirect_url'));
                    $tokenResponse = Http::asForm()->post('https://api.dailymotion.com/oauth/token', [
                        'grant_type' => 'authorization_code',
                        'client_id' => config('platforms.dailymotion.client_key'),
                        'client_secret' => config('platforms.dailymotion.client_secret'),
                        'redirect_uri' => $redirect,
                        'code' => $code,
                    ]);
                    if ($tokenResponse->failed()) {
                        abort(401, $tokenResponse->body());
                    }
                    $tokens = $tokenResponse->json();
                    $accessToken = $tokens['access_token'];
                    $refreshToken = $tokens['refresh_token'] ?? null;

                    $meResponse = Http::withToken($accessToken)->get('https://api.dailymotion.com/user/me', [
                        'fields' => 'id',
                    ]);
                    if ($meResponse->failed()) {
                        abort(401, $meResponse->body());
                    }
                    $channelId = $meResponse->json('id');
                    self::breakIfChannelClaimed($channelId, Platform::Dailymotion);

                    CreatorSource::create([
                        'source_name' => Platform::Dailymotion->value,
                        'external_channel_id' => $channelId,
                        'creator_id' => Auth::user()->creator->id,
                        'access_token' => $accessToken,
                        'refresh_token' => $refreshToken,
                    ]);

                    return response()->json(['message' => 'success']);
                } catch (Exception $e) {
                    if ($e->getCode() == 403) {
                        abort(403, $e->getMessage());
                    }
                    abort(401, $e->getMessage());
                }

            case Platform::Vimeo->value:
                try {
                    $vimeo = new AuthVimeo(AuthVimeo::getAccessTokenWithCode($code)['access_token']);

                    $vm_channel_id = $vimeo->getMyCreator()->id;

                    self::breakIfChannelClaimed($vm_channel_id, Platform::Vimeo);

                    CreatorSource::create([
                        'source_name' => Platform::Vimeo->value,
                        'external_channel_id' => $vm_channel_id,
                        'creator_id' => Auth::user()->creator->id,
                        'access_token' => $vimeo->access_token,
                        'refresh_token' => null,
                    ]);

                    return response()->json(['message' => 'success']);
                } catch (Exception $e) {
                    return response()->json(['message' => $e->getMessage()]);
                }
            case Platform::Twitch->value:
                try {
                    $tokens = AuthTwitch::getAccessTokenWithCode($code);

                    $twitch = new AuthTwitch($tokens['access_token']);
                    $twitch_channel_id = $twitch->getMyCreator()->id;

                    self::breakIfChannelClaimed($twitch_channel_id, Platform::Twitch);

                    CreatorSource::create([
                        'source_name' => Platform::Twitch->value,
                        'external_channel_id' => $twitch_channel_id,
                        'creator_id' => Auth::user()->creator->id,
                        'access_token' => $tokens['access_token'],
                        'refresh_token' => $tokens['refresh_token'],
                    ]);

                    return response()->json(['message' => 'success']);
                } catch (Exception $e) {
                    return response()->json(['error' => $e->getMessage()]);
                }

            default:
                abort(400);
        }
    }

    public function claim(Request $request, string $platform): JsonResponse
    {
        $platformEnum = Platform::fromValue($platform);
        if (! PlatformRegistry::isClaimPlatform($platformEnum)) {
            abort(400, 'Platform does not support channel claim.');
        }

        $validated = $request->validate([
            'channel_id' => ['required', 'string', 'max:256'],
        ]);
        $channelId = trim($validated['channel_id']);
        if ($platformEnum === Platform::Odysee) {
            $channelId = trim(ltrim($channelId, '@'));
        }

        self::breakIfChannelClaimed($channelId, $platformEnum);

        CreatorSource::create([
            'source_name' => $platformEnum->value,
            'external_channel_id' => $channelId,
            'creator_id' => Auth::user()->creator->id,
            'access_token' => null,
            'refresh_token' => null,
        ]);

        return response()->json(['message' => 'success']);
    }

    public function unlink(string $platform)
    {
        $source = CreatorSource::where('creator_id', Auth::user()->creator->id)
            ->where('source_name', $platform)->first();

        if (! $source) {
            abort(404);
        }

        $source->delete();

        return response()->json(['message' => 'success']);
    }

    private static function breakIfChannelClaimed(string $externalChannelID, Platform $platform): void
    {
        $existing = CreatorSource::where('external_channel_id', $externalChannelID)
            ->where('source_name', $platform->value)
            ->first();

        if (! $existing) {
            return;
        }
        $user = $existing->creator->user();

        if ($user->exists()) {
            if ($user->first()->id == auth()->user()->id) {
                throw new Exception('You have already claimed this channel', 403);
            }
            throw new Exception('That channel has already been claimed by another user', 403);
        }
    }

    public function logIn(string $platform)
    {
        try {
            $class = Platform::fromValue($platform)->getPlatformAuthClass();
        } catch (InvalidArgumentException $e) {
            abort(400, $e->getMessage());
        }

        if (! method_exists($class, 'getLoginUrl')) {
            abort(400);
        }

        $auth_url = $class::getLoginUrl();

        if (! isset($auth_url)) {
            abort(400);
        }

        return ['url' => $auth_url];
    }
}
