<?php

namespace OpenEd;


class SignedServerRequest
{
    private $client_id;
    private $client_secret;

    public function __construct($client_id, $client_secret)
    {
        $this->client_secret = $client_secret;
        $this->client_id = $client_id;
    }

    private static function base64UrlEncode($input)
    {
        return str_replace('+', '-', str_replace('/', '_', preg_replace('/=+$/', '', base64_encode($input))));
    }

    private static function generateToken($username)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes(64));
        } elseif (function_exists('random_bytes')) {
            return bin2hex(random_bytes(64));
        } else {
            return sha1($username);
        }
    }

    public function generateSignedRequest($username)
    {
        $envelope = self::base64UrlEncode(json_encode([
            'username' => $username,
            # user can have association with the school by supplying NCES_ID
            # 'school_nces_id' => '<nces_id>',
            'client_id' => $this->client_id,
            'token' => self::generateToken($username),
            'algorithm' => 'HMAC-SHA256'
        ]));

        $signature = self::base64UrlEncode(hash_hmac('SHA256', $envelope, $this->client_secret));

        return "$signature.$envelope";
    }
}
