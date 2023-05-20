<?php

class JWTToken
{
    // Private key for signing JWT
    private $private_key = '#WinyDev12345!';
    // Token expiration time in seconds
    private $expiration_time = 604800;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function generate_token($data)
    {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        $payload = json_encode([
            'iss' => base_url(),
            'aud' => base_url(),
            'iat' => time(),
            'exp' => time() + $this->expiration_time,
            'data' => $data
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->private_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
        return $jwt;
    }

    public function validate_token($token)
    {
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = explode('.', $token);

        $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlHeader)), true);
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);

        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlSignature));
        $expected_signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->private_key, true);

        if ($signature !== $expected_signature) {
            return false;
        }

        if ($payload['exp'] < time()) {
            return false;
        }

        return $payload['data'];
    }
}
