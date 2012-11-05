<?php

namespace Mute\Facebook;

use Mute\Facebook\Requestable;

class Batch implements Requestable
{
    /**
     * @var GraphApi
     */
    protected $api;

    /**
     * @var array
     */
    protected $queries;

    /**
     * @var array
     */
    protected $attachedFiles;

    public function __construct(GraphApi $api)
    {
        $this->api = $api;
        $this->queries = array();
        $this->attachedFiles = array();
    }

    public function get($path, array $params = null)
    {
        return $this->query($path, 'GET', $params);
    }

    public function post($path, array $params = null, array $files = null)
    {
        return $this->query($path, 'POST', $params, $files);
    }

    public function put($path, array $params = null, array $files = null)
    {
        return $this->query($path, 'PUT', $params, $files);
    }

    public function delete($path, array $params = null)
    {
        return $this->query($path, 'DELETE', $params);
    }

    public function query($path, $method, array $params = null, array $files = null)
    {
        $query = array(
            'method' => $method,
            'relative_url' => $path
        );

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
        $this->queries[] = $query;

        return $this;
    }

    public function send()
    {
        return $this->api->raw('/', $this->queries, $this->attachedFiles)->parseJson();
    }
}
