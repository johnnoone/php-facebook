<?php

namespace Mute\Facebook;

use Mute\Facebook\Exception\InvalidArgumentException;

class Util
{
    public static function makeAppSecretProof($access_token, $app_secret)
    {
        return hash_hmac('sha256', $access_token, $app_secret);
    }

    public static function makeSignedRequest(array $data, $app_secret)
    {
        $data += array(
            'algorithm' => 'HMAC-SHA256',
            'issued_at' => time(),
        );

        if ($data['algorithm'] !== 'HMAC-SHA256') {
            throw new InvalidArgumentException('Unsupported algorithm ' . $data['algorithm']);
        }

        $payload = static::encodeBase64(json_encode($data));
        unset($data);
        $raw_sig = hash_hmac('sha256', $payload, $app_secret, true);
        $signature = static::encodeBase64($raw_sig);

        return $signature . '.' . $payload;
    }

    public static function parseSignedRequest($signed_request, $app_secret)
    {
        $parts = explode('.', $signed_request, 2);
        if (!isset($parts[1])) {
            throw new OAuthSignatureException('Invalid (incomplete) signature data');
        }
        list($encoded_sig, $payload) = $parts;
        unset($parts);

        $data = json_decode(static::decodeBase64($payload), true);
        if ($data['algorithm'] !== 'HMAC-SHA256') {
            throw new InvalidArgumentException('Unsupported algorithm ' . $data['algorithm']);
        }

        $signature = static::decodeBase64($encoded_sig);
        $expected_sig = hash_hmac('sha256', $payload, $app_secret, true);
        if ($signature !== $expected_sig) {
            throw new OAuthSignatureError('Invalid signature');
        }

        return $data;
    }

    public static function encodeBase64($data)
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }

    public static function decodeBase64($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public static function curlFile($filename, $mimetype=null, $postname=null)
    {
        // this function has been implemented in PHP >= 5.5
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename, $mimetype, $postname);
        }
        else {
            return "@$filename;filename="
                . ($postname ?: basename($filename))
                . ($mimetype ? ";type=$mimetype" : '');
        }
    }
}
