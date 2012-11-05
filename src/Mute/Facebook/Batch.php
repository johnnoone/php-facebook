<?php

namespace Mute\Facebook;

use InvalidArgumentException;
use Mute\Facebook\Bases\AccessToken;
use Mute\Facebook\Bases\Requestable;
use Mute\Facebook\Bases\RequestHandler;
use Mute\Facebook\Bases\RequestHandlerAware;

class Batch implements AccessToken, Requestable, RequestHandlerAware
{

    protected $queries;
    protected $attachedFiles;
    protected $fallbackAccessToken;

    /**
     * @var RequestHandler
     */
    protected $requestHandler;

    public function __construct($access_token, RequestHandler $requestHandler)
    {
        $this->queries = array();
        $this->attachedFiles = array();
        $this->fallbackAccessToken = $access_token;
        $this->requestHandler = $requestHandler;
    }

    public function get($path, array $parameters = null, $batchParams = null)
    {
        $this->append($path, 'GET', $parameters, null, $batchParams);

        return $this;
    }

    public function post($path, array $parameters = null, array $files = null, $batchParams = null)
    {
        $this->append($path, 'POST', $parameters, $files, $batchParams);

        return $this;
    }

    public function put($path, array $parameters = null, array $files = null, $batchParams = null)
    {
        $this->append($path, 'PUT', $parameters, $files, $batchParams);

        return $this;
    }

    public function delete($path, array $parameters = null, $batchParams = null)
    {
        $this->append($path, 'DELETE', $parameters, null, $batchParams);

        return $this;
    }

    public function fql($query, array $parameters = null, $batchParams = null)
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
        $this->append($path, 'POST', $parameters, null, $batchParams);

        return $this;
    }

    public function execute()
    {
        $parameters = array(
            'access_token' => $this->fallbackAccessToken,
            'batch' => $this->queries,
        );
        $files = $this->attachedFiles;

        $response = $this->requestHandler->request($path, $parameters, $files);
        $this->queries = array();
        $this->attachedFiles = array();

        return $response;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $params
     * @param array $files
     * @param string|array $batchParams
     * @return mixed
     * @todo throw an Exception when max queries is reached
     */
    protected function append($path, $method, array $params = null, array $files = null, $batchParams = null)
    {
        if (is_array($batchParams)) {
            $batchParams = array_intersect_key($batchParams, array(
                'name' => true,
                'omit_response_on_success' => true,
            );
        }
        else (is_string($batchParams)) {
            $batchParams = array(
                'name' => $batchParams,
            );
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
        if (is_string($name)) {
            $query['name'] = $name;
        }
        if (is_bool($omit_response_on_success)) {
            $query['omit_response_on_success'] = $omit_response_on_success;
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
