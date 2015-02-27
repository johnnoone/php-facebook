<?php

namespace Mute\Facebook;

use Closure;
use Exception;
use Mute\Facebook\Bases\AccessToken;
use Mute\Facebook\Bases\Batchable;
use Mute\Facebook\Bases\Configurable;
use Mute\Facebook\Bases\Requestable;
use Mute\Facebook\Bases\RequestHandler;
use Mute\Facebook\Bases\Versionned;
use Mute\Facebook\Exception\InvalidArgumentException;
use Mute\Facebook\Exception\OAuthSignatureException;
use Mute\Facebook\Util;

class App implements AccessToken, Batchable, Configurable, Requestable, RequestHandler, Versionned
{
    protected $id;
    protected $secret;
    protected $namespace;
    protected $api = 'https://graph.facebook.com';
    protected $version;

    public $accessToken;
    public $useAppSecretProof = true;

    /**
     * @var RequestHandler
     */
    protected $requestHandler;

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

    function __construct($app_id, $app_secret, $app_namespace = null, $version = null)
    {
        $this->id = $app_id;
        $this->secret = $app_secret;
        $this->namespace = $app_namespace;
        $this->accessToken = $this->id . '|' . $this->secret;
        $this->initialOptions = $this->globalOptions;
        $this->version = $version;
        $this->requestHandler = new Req\CurlRequest();
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

    public function getVersion()
    {
        return $this->version;
    }

    public function changeVersion($version = null)
    {
        $app = clone $this;
        $app->version = $version;
        return $app;
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
        $url = $this->requestURL($path);
        $parameters = $this->requestParameters($parameters);
        $options = $this->requestOptions($options);

        $resp = $this->requestHandler->request($url, $parameters, $files, $headers, $options);

        return $resp;
    }

    protected function requestURL($path)
    {
        $url = rtrim($this->api, '/');
        if ($version = $this->getVersion()) {
            $url .= '/' . $version;
        }
        $url .= '/' . ltrim($path, '/');

        return $url;
    }

    protected function requestParameters(array $parameters = null)
    {
        $parameters = (array) $parameters;
        if ($this->useAppSecretProof && isset($parameters['access_token'])) {
            $proof = Util::makeAppSecretProof($parameters['access_token'],
                                              $this->getSecret());
            $parameters['appsecret_proof'] = $proof;
        }

        return $parameters;
    }

    protected function requestOptions(array $options = null)
    {
        return is_array($options)
            ? array_merge($this->globalOptions, $options)
            : $this->globalOptions;
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
