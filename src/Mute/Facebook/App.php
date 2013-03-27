<?php

namespace Mute\Facebook;

use Closure;
use Exception;
use Mute\Facebook\Bases\AccessToken;
use Mute\Facebook\Bases\Batchable;
use Mute\Facebook\Bases\Configurable;
use Mute\Facebook\Bases\Requestable;
use Mute\Facebook\Bases\RequestHandler;
use Mute\Facebook\Exception\CurlException;
use Mute\Facebook\Exception\GraphAPIException;
use Mute\Facebook\Exception\HTTPException;
use Mute\Facebook\Exception\InvalidArgumentException;
use Mute\Facebook\Exception\OAuthSignatureException;
use Mute\Facebook\Util;

class App implements AccessToken, Batchable, Configurable, Requestable, RequestHandler
{
    protected $id;
    protected $secret;
    protected $namespace;
    protected $api = "https://graph.facebook.com";

    const OPT_CONNECT_TIMEOUT = 'connect_timeout';
    const OPT_TIMEOUT = 'timeout';
    const OPT_UPLOAD_BOOT = 'upload_boot';

    /**
     * Theses options will be used by every calls that use the current
     * requestHandler (Batches, AuthenticatedGraphApi...).
     *
     * If you need case by case granularity, considere to fork the current
     * requestHandler with self::getAuthenticatedGraphApi, and then manipulate
     * his local options.
     *
     * @var array
     */
    protected $globalOptions = array(
        self::OPT_CONNECT_TIMEOUT => 5,     // in seconds. connect timeout
        self::OPT_TIMEOUT => 10,            // in seconds. timeout for connect + response
        self::OPT_UPLOAD_BOOT => 64300,     // bytes per seconds. heuristic for file uploading, 64300 is OK for 512 Kps.
    );

    /**
     * Used when resetting options
     *
     * @var array
     */
    private $initialOptions;

    function __construct($app_id, $app_secret, $app_namespace = null)
    {
        $this->id = $app_id;
        $this->secret = $app_secret;
        $this->namespace = $app_namespace;
        $this->accessToken = $this->id . '|' . $this->secret;
        $this->initialOptions = $this->globalOptions;
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

    public function getOptions()
    {
        return $this->globalOptions;
    }

    public function setOptions($name, $value = null)
    {
        if (is_array($name)) {
            $this->globalOptions = array_merge($this->globalOptions, $name);
        }
        elseif (is_string($name)) {
            $this->globalOptions[$name] = $value;
        }
        else {
            throw new InvalidArgumentException('first argument must be an array or a string');
        }

        return $this;
    }

    public function resetOptions()
    {
        $this->globalOptions = $this->initialOptions;

        return $this;
    }

    public function get($path, array $parameters = null, $headers = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'GET',
        );

        return $this->request($path, $parameters, null, $headers);
    }

    public function post($path, array $parameters = null, array $files = null, $headers = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'POST',
        );

        return $this->request($path, $parameters, $files, $headers);
    }

    public function put($path, array $parameters = null, array $files = null, $headers = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'PUT',
        );

        return $this->request($path, $parameters, $files, $headers);
    }

    public function delete($path, array $parameters = null, $headers = null)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'DELETE',
        );

        return $this->request($path, $parameters, null, $headers);
    }

    public function fql($query, array $parameters = null, $headers = null)
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

        return $this->request('fql', $parameters, null, $headers);
    }

    public function batch(Closure $commands = null, $extended = false)
    {
        $batch = new Batch($this->accessToken, $this);
        if ($commands) {
            $commands($batch);
            $batch = $batch->execute($extended);
        }

        return $batch;
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

    public function makeSignedRequest(array $data)
    {
        try {

            return Util::makeSignedRequest($data, $this->secret);
        }
        catch (Exception $e) {

            throw new OAuthSignatureException($e->getMessage(), 0, $e);
        }
    }

    function parseSignedRequest($signed_request)
    {
        try {

            return Util::parseSignedRequest($signed_request, $this->secret);
        }
        catch (Exception $e) {

            throw new OAuthSignatureException($e->getMessage(), 0, $e);
        }
    }

    public function request($path, array $parameters = null, array $files = null, $headers = null, array $options = null)
    {
        $method = 'POST';
        if (isset($parameters['method'])) {
            $method = $parameters['method'];
            unset($parameters['method']);
        }

        if (!$headers) {
            $headers = array();
            $extended = false;
        }
        elseif (is_array($headers)) {
            $extended = true;
        }
        elseif (is_bool($headers)) {
            $extended = $headers;
            $headers = array();
        }
        else {
            throw new InvalidArgumentException('$headers must be a bool or an array');
        }

        $options = is_array($options)
            ? array_merge($this->globalOptions, $options)
            : $this->globalOptions;

        $options = filter_var_array($options, array(
            'connect_timeout' => FILTER_VALIDATE_INT,
            'timeout' => FILTER_VALIDATE_INT,
            'upload_boot' => FILTER_VALIDATE_INT,
        ));

        $curlOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => "Mute/Facebook-1.0 (https://github.com/johnnoone/php-facebook)",
            CURLOPT_CONNECTTIMEOUT => $options['connect_timeout']
                ? $options['connect_timeout']
                : 5,
            CURLOPT_TIMEOUT => $options['timeout']
                ? $options['timeout']
                : 5,
            CURLOPT_MAXREDIRS => 10,        // stop after 10 redirects
            CURLOPT_FAILONERROR => false,   // lets 4** http codes be processed
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => $extended,
            CURLOPT_CUSTOMREQUEST => $method,
        );

        $postFields = array();
        if ($parameters) foreach ($parameters as $name => $param) {
            $postFields[$name] = is_scalar($param)
                ? $param
                : json_encode($param);
        }
        if ($files) foreach ($files as $name => $file) {
            $postFields[$name] = '@' . realpath($file);
            // On *nix it's hopefully not affected by PHP time limit,
            // but on Windows try to send the smallest files you can.
            if ($boost = $options['upload_boot']) {
                $curlOptions[CURLOPT_TIMEOUT] += filesize($file) / $boost;
            }
        }

        $url = $this->api . '/' . ltrim($path, '/');
        if ($postFields) {
            if ($method == 'GET') {
                $url .= strpos('?', $url) === false ? '?' : '&';
                $url .= http_build_query($postFields, null, '&');
            }
            else {
                $curlOptions[CURLOPT_POSTFIELDS] = $files
                    ? $postFields
                    : http_build_query($postFields, null, '&');
            }
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $curlOptions);

        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $info    = curl_getinfo($ch);
        curl_close($ch);

        if ($err !== CURLE_OK) {

            throw new CurlException($errmsg, $err);
        }

        if ($extended) {
            $header = substr($content, 0, $info['header_size']);
            $content = substr($content, -$info['download_content_length']);
        }

        if (in_array($info['content_type'], array(
            'text/javascript; charset=UTF-8',
            'application/json; charset=UTF-8',
            'application/json',
        ))) {
            $content = json_decode($content, true);
            if (is_array($content) && isset($content['error'])) {

                throw new GraphAPIException($content['error']);
            }
        }

        if ($info['http_code'] >= 400) {

            throw new HTTPException($info['http_code'], $content);
        }

        if ($extended) {
            $headers = $this->parseHeader($header);
            return array(
                'code' => $info['http_code'],
                'headers' => $headers,
                'body' => $content,
            );
        }

        return $content;
    }

    protected function parseHeader($header)
    {
        if (function_exists('http_parse_headers')) {
            return http_parse_headers($header);
        }

        $response = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach ($fields as $field) if (preg_match('/(?P<key>[^:]+): (?<value>.+)/m', $field, $match)) {
            if (isset($response[$match['key']])) {
                if (!is_array($response[$match['key']])) {
                    $response[$match['key']] = array($response[$match['key']]);
                }
                $response[$match['key']][] = trim($match['value']);
            }
            else {
                $response[$match['key']] = trim($match['value']);
            }
        }

        return $response;
    }

    /**
     * @return AuthenticatedGraphApi
     */
    public function getAuthenticatedGraphApi($access_token)
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
