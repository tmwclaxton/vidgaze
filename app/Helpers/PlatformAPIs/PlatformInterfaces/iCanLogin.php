<?php

namespace App\Helpers\PlatformAPIs\PlatformInterfaces;

interface iCanLogin
{
    public static function getLogInUrl(array $scopes = null, string $redirect_url_path = null);

    public static function getRefreshAccessToken($refreshToken): array;

}
