<?php

namespace Mute\Facebook;

use Mute\Facebook\Bases\AccessToken;
use Mute\Facebook\Bases\Batchable;
use Mute\Facebook\Bases\Requestable;
use Mute\Facebook\Bases\RequestHandler;
use Mute\Facebook\Exception\CurlException;
use Mute\Facebook\Exception\GraphAPIException;
use Mute\Facebook\Exception\HTTPException;
use Mute\Facebook\Exception\InvalidArgumentException;
use Mute\Facebook\Exception\OAuthSignatureException;

class App implements AccessToken, Batchable, Requestable, RequestHandler
{
    protected $id;
    protected $secret;
    protected $namespace;
    protected $api = "https://graph.facebook.com";
    protected $curlOpts = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => "Mute/Facebook (https://github.com/johnnoone/php-mute-facebook)",
        CURLOPT_CONNECTTIMEOUT => 10,          // timeout on connect
        CURLOPT_TIMEOUT        => 10,          // timeout on response
        CURLOPT_MAXREDIRS      => 10,          // stop after 10 redirects
        CURLOPT_FAILONERROR    => false,       // lets 4** http codes be processed
    );

    function __construct($app_id, $app_secret, $app_namespace)
    {
        $this->id = $app_id;
        $this->secret = $app_secret;
        $this->namespace = $app_namespace;
        $this->accessToken = $this->id . '|' . $this->secret;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getApi()
    {
        return $this->api;
    }

    public function get($path, array $parameters = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'GET',
        );

        return $this->request($path, $parameters);
    }

    public function post($path, array $parameters = null, array $files = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'POST',
        );

        return $this->request($path, $parameters, $files);
    }

    public function put($path, array $parameters = null, array $files = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'PUT',
        );

        return $this->request($path, $parameters, $files);
    }

    public function delete($path, array $parameters = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'DELETE',
        );

        return $this->request($path, $parameters);
    }

    public function fql($query, array $parameters = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'GET',
        );

        if (is_array($query)) {
            $query = json_encode($query);
            if (strpos($query, '[') === 0) {
                /**
                 * fql multiqueries only accept json array.
                 * @link https://developers.facebook.com/docs/reference/rest/fql.multiquery/
                 */
                throw new InvalidArgumentException('$query is interpreted as a json list, convert his keys');
            }
        }
        $parameters['q'] = $query;

        return $this->request('fql', $parameters);
    }

    /**
     * @return Batch
     */
    public function batch()
    {
        return new Batch($this->accessToken, $this);
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->accessToken = $access_token;

        return $this;
    }

    function parseSignedRequest($signed_request)
    {
        static $base64url_decode;
        if (!isset($base64url_decode)) {
            $base64url_decode = function ($data) {
              return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
            };
        }

        $parts = explode('.', $signed_request, 2);
        if (!isset($parts[1])) {
            throw new OAuthSignatureException('Invalid (incomplete) signature data');
        }
        list($encoded_sig, $payload) = $parts;
        unset($parts);

        $data = json_decode($base64url_decode($payload), true);
        if ($data['algorithm'] !== 'HMAC-SHA256') {
            throw new OAuthSignatureException('Unsupported algorithm ' . $data['algorithm']);
        }

        $signature = $base64url_decode($encoded_sig);
        $expected_sig = hash_hmac('sha256', $payload, $this->secret, true);
        if ($signature !== $expected_sig) {
            throw new OAuthSignatureError('Invalid signature');
        }

        return $data;
    }

    public function request($path, array $parameters = null, array $files = null)
    {
        $curlOptions = $this->curlOpts;

        $postFields = array();
        if ($parameters) foreach ($parameters as $name => $param) {
            $postFields[$name] = is_scalar($param)
                ? $param
                : json_encode($param);
        }
        if ($files) foreach ($files as $name => $file) {
            $postFields[$name] = '@' . realpath($file);
        }
        if ($postFields) {
            $curlOptions[CURLOPT_POSTFIELDS] = $files
                ? $postFields
                : http_build_query($postFields, null, '&');
        }

        $ch = curl_init($this->api . '/' . ltrim($path, '/'));
        curl_setopt_array($ch, $curlOptions);

        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);

        if ($err !== CURLE_OK) {

            throw new CurlException($errmsg, $err);
        }

        if ($header['content_type'] == 'text/javascript; charset=UTF-8') {
            $content = json_decode($content, true);
            if (is_array($content) && isset($content['error'])) {

                throw new GraphAPIException($content['error']);
            }
        }

        if ($header['http_code'] >= 400) {

            throw new HTTPException($header['http_code'], $content);
        }

        return $content;
    }

    /**
     * @return AuthenticatedGraphApi
     */
    public function getOAuthenticatedGraphApi($access_token)
    {

        return new AuthenticatedGraphApi($access_token, $this);
    }

    public function getOAuth()
    {
        return new OAuth($this);
    }

    public function getConnect()
    {
        return new Connect($this);
    }
}
