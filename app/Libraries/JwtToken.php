<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtToken
{

    public static function encodeTokenJwt(array $payload): string
    {
        $key = getenv('app.keyJwt');
        return JWT::encode($payload, $key, 'HS256');
    }

    public static function decodeTokenJwt(string $tokenJwt): array
    {
        $key = getenv('app.keyJwt');
        return (array) JWT::decode(str_replace('Bearer ', '', $tokenJwt), new Key($key, 'HS256'));
    }
}
