<?php

namespace Mute\Facebook;

use Closure;
use Mute\Facebook\Bases\AccessToken;
use Mute\Facebook\Bases\Batchable;
use Mute\Facebook\Bases\Requestable;
use Mute\Facebook\Bases\RequestHandler;
use Mute\Facebook\Bases\RequestHandlerAware;
use Mute\Facebook\Exception\InvalidArgumentException;

/**
 * Simple App wrapper which inject relevant access_token.
 */
class AuthenticatedGraphApi implements AccessToken, Batchable, Requestable, RequestHandlerAware
{
    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var RequestHandler
     */
    protected $requestHandler;

    function __construct($access_token, RequestHandler $requestHandler)
    {
        $this->accessToken = $access_token;
        $this->requestHandler = $requestHandler;
    }

    /**
     * {@inheritdoc}
     * @param bool $extended return the extended response?
     */
    public function get($path, array $parameters = null, $headers = false)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'GET',
        );

        return $this->requestHandler->request($path, $parameters, null, $headers);
    }

    /**
     * {@inheritdoc}
     * @param bool $extended return the extended response?
     */
    public function post($path, array $parameters = null, array $files = null, $headers = false)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'POST',
        );

        return $this->requestHandler->request($path, $parameters, $files, $headers);
    }

    /**
     * {@inheritdoc}
     * @param bool $extended return the extended response?
     */
    public function put($path, array $parameters = null, array $files = null, $headers = false)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'PUT',
        );

        return $this->requestHandler->request($path, $parameters, $files, $headers);
    }

    /**
     * {@inheritdoc}
     * @param bool $extended return the extended response?
     */
    public function delete($path, array $parameters = null, $headers = false)
    {
        $parameters = (array) $parameters;
        $parameters += array(
            'access_token' => $this->accessToken,
            'method' => 'DELETE',
        );

        return $this->requestHandler->request($path, $parameters, null, $headers);
    }

    /**
     * {@inheritdoc}
     * @link https://developers.facebook.com/docs/technical-guides/fql/
     * @param bool $extended return the extended response?
     */
    public function fql($query, array $parameters = null, $headers = false)
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

        return $this->requestHandler->request('fql', $parameters, null, $headers);
    }

    public function batch(Closure $commands = null, $extended = false)
    {
        $batch = new Batch($this->accessToken, $this->requestHandler);
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

    public function getRequestHandler()
    {
        return $this->requestHandler;
    }

    /**
     * @param RequestHandler $requestHandler
     */
    public function setRequestHandler(RequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }
}
