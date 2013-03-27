<?php

namespace Mute\Facebook;

use Mute\Facebook\Bases\AccessToken;
use Mute\Facebook\Bases\Configurable;
use Mute\Facebook\Bases\Requestable;
use Mute\Facebook\Bases\RequestHandler;
use Mute\Facebook\Bases\RequestHandlerAware;
use Mute\Facebook\Exception\InvalidArgumentException;

class Batch implements AccessToken, Configurable, Requestable, RequestHandlerAware
{
    protected $queries;
    protected $attachedFiles;
    protected $fallbackAccessToken;

    /**
     * @var RequestHandler
     */
    protected $requestHandler;

    /**
     * @var array
     */
    protected $localOptions;

    public function __construct($access_token, RequestHandler $requestHandler, array $options = null)
    {
        $this->queries = array();
        $this->attachedFiles = array();
        $this->fallbackAccessToken = $access_token;
        $this->requestHandler = $requestHandler;
        $this->localOptions = (array) $options;
    }

    public function getOptions()
    {
        return $this->localOptions;
    }

    public function setOptions($name, $value = null)
    {
        if (is_array($name)) {
            $this->localOptions = array_merge($this->localOptions, $name);
        }
        elseif (is_string($name)) {
            $this->localOptions[$name] = $value;
        }
        else {
            throw new InvalidArgumentException('first argument must be an array or a string');
        }

        return $this;
    }

    public function resetOptions()
    {
        $this->localOptions = array();

        return $this;
    }

    public function get($path, array $parameters = null, $headers = null, $batchParams = null)
    {
        $this->append($path, 'GET', $parameters, null, $headers, $batchParams);

        return $this;
    }

    public function post($path, array $parameters = null, array $files = null, $headers = null, $batchParams = null)
    {
        $this->append($path, 'POST', $parameters, $files, $headers, $batchParams);

        return $this;
    }

    public function put($path, array $parameters = null, array $files = null, $headers = null, $batchParams = null)
    {
        $this->append($path, 'PUT', $parameters, $files, $headers, $batchParams);

        return $this;
    }

    public function delete($path, array $parameters = null, $headers = null, $batchParams = null)
    {
        $this->append($path, 'DELETE', $parameters, null, $headers, $batchParams);

        return $this;
    }

    public function fql($query, array $parameters = null, $headers = null, $batchParams = null)
    {
        $parameters = (array) $parameters;
        if (is_array($query)) {
            $query = json_encode($query);
            if (strpos($query, '[') === 0) {
                /**
                 * fql multiqueries only accept json array.
                 * @link https://developers.facebook.com/docs/reference/rest/fql.multiquery/
                 */
                throw new InvalidArgumentException('$query is interpreted as a json list, convert his keys');
            }

            $path = 'method/fql.multiquery';
            $parameters['queries'] = $query;
        }
        else {
            $path = 'method/fql.query';
            $parameters['query'] = $query;
        }
        $this->append($path, 'POST', $parameters, null, $headers, $batchParams);

        return $this;
    }

    /**
     * @param bool $extended should we return the full response or bodies only ?
     * @return array
     */
    public function execute($extended = false)
    {
        $parameters = array(
            'access_token' => $this->fallbackAccessToken,
            'batch' => $this->queries,
        );
        $files = $this->attachedFiles;

        $response = $this->requestHandler->request('', $parameters, $files, null, $this->localOptions);
        $this->queries = array();
        $this->attachedFiles = array();
        if ($extended) {
            return $response;
        }

        // try to parse bodies...
        $response = array_map(function($response) {
            if ($response === null) {
                return null;
            }

            $body = $response['body'];
            foreach ($response['headers'] as $header) if (
                     $header['name'] == 'Content-Type' &&
                     in_array($header['value'], array(
                         'text/javascript; charset=UTF-8',
                         'application/json; charset=UTF-8',
                         'application/json',
                     ))) {

                return json_decode($body, true);
            }

            return $body;
        }, $response);

        return $response;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $params
     * @param array $files
     * @param array $headers
     * @param string|array $batchParams
     * @return mixed
     * @todo throw an Exception when max queries is reached
     */
    protected function append($path, $method, array $params = null, array $files = null, $headers = null, $batchParams = null)
    {
        if (is_array($batchParams)) {
            $batchParams = array_intersect_key($batchParams, array(
                'name' => true,
                'omit_response_on_success' => true,
            ));
        }
        elseif (is_string($batchParams)) {
            $batchParams = array(
                'name' => $batchParams,
            );
        }
        else {
            $batchParams = array();
        }

        $query = array(
            'method' => $method,
            'relative_url' => ltrim($path, '/'),
        ) + $batchParams;

        if ($params) {
            $params = array_map(function ($param) {
                return is_string($param) ? $param : json_encode($param);
            }, $params);

            $qvars = http_build_query($params, '', '&');
            if (in_array($method, array('GET', 'DELETE'))) {
                $query['relative_url'] .= (strpos($path, '?') === false ? '?' : '&') . $qvars;
            }
            else {
                $query['body'] = $qvars;
            }
            unset($qvars);
        }
        if ($files) {
            $currents = count($this->attachedFiles);
            $attachedFiles = array();
            foreach ($files as $value) {
                $filename = 'file' + $currents++;
                $attachedFiles[] = $filename;
                $this->attachedFiles[$filename] = $value;
            }
            $query['attached_files'] = implode(',', $attachedFiles);
        }
        if ($headers) {
            if (!is_array($headers)) {
                throw new InvalidArgumentException('$headers must be an array');
            }
            $query['headers'] = $headers;
        }

        $this->queries[] = $query;

        return $this;
    }

    public function getAccessToken()
    {
        return $this->fallbackAccessToken;
    }

    public function setAccessToken($access_token)
    {
        $this->fallbackAccessToken = $access_token;

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
