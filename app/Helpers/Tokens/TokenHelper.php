<?php

namespace App\Helpers\Tokens;

class TokenHelper
{
    public static function generateToken($sessionId, $creatorID, $videoSlug)
    {
        // Use a secret key and the session ID, and video slug to generate a unique token
        return hash_hmac('sha256', $sessionId . ':' . $creatorID . ':' . $videoSlug, 'secret_key');
    }
}
