<?php

namespace Mute\Facebook;

use ArrayAccess;
use Exception;
use Serializable;

use Mute\Facebook\Requestable,
    Mute\Facebook\Response;

class GraphApi implements ArrayAccess, Serializable, Requestable
{
    /**
     * @var string
     */
    protected $api = "https://graph.facebook.com";

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $connectionTimeout = 30;

    /**
     * @var int
     */
    protected $transactionTimeout = 30;

    /**
     * @var array
     */
    protected $fallbacks;

    function __construct($api = null, array $fallbacks = null)
    {
        $this->api = (array) ($api ? $api : $this->api);
        $this->fallbacks = (array) $fallbacks;
        $this->init();
    }

    function init()
    {
        $parsed = parse_url($this->api[array_rand($this->api)]);
        $this->scheme = isset($parsed['scheme']) && $parsed['scheme'] == "http" ? "http" : "ssl";
        $this->host = $parsed['host'];
        $this->port = isset($parsed['port']) ? $parsed['port'] : ($this->scheme == "http" ? 80 : 443);

        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $qvars);
            $this->fallbacks += $qvars;
        }
    }

    function get($path, array $params = null)
    {
        return $this->query($path, 'GET', $params);
    }

    function post($path, array $params = null, array $files = null)
    {
        return $this->query($path, 'POST', $params, $files);
    }

    function put($path, array $params = null, array $files = null)
    {
        return $this->query($path, 'PUT', $params, $files);
    }

    function delete($path, array $params = null)
    {
        return $this->query($path, 'DELETE', $params);
    }

    function query($path, $method, array $params = null, array $files = null)
    {
        $path = '/' . ltrim($path, '/');
        $params = (array) $params;
        $params['method'] = $method;

        return $this->raw($path, $params, $files)->parseJson();
    }

    function raw($path, array $params = null, array $files = null)
    {
        $params = (array) $params + $this->fallbacks;
        $params = array_map(function ($param) {
            return is_string($param) ? $param : json_encode($param);
        }, $params);

        if ($files) {
            $boundary = "IKissedAGirlAndILikedItTheTasteOfHerCherryChapStick";
            $body = '';

            foreach ($params as $key => $value) {
                $body .= '--' . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="' . $key . '"' . "\r\n";
                $body .= 'Content-Length: ' . strlen($value) . "\r\n\r\n" . $value . "\r\n";
            }

            foreach ($files as $key => $value) {
                if (is_string($value)) {
                    $filename = ($pos = strrpos($value, '/'))
                         ? substr($value, $pos + 1)
                         : $value;

                    $content = file_get_contents($value);
                }
                if (is_resource($value)) {
                    $meta = stream_get_meta_data($value);
                    switch ($meta['wrapper_type']) {
                        case 'http':
                        case 'plainfile':
                            break;
                        default:
                            throw new Exception('This type of resource is not implemented: ' . $type);
                    }

                    if ($meta['seekable']) {
                        rewind($value);
                    }

                    if ($meta['uri']) {
                        $filename = ($pos = strrpos($meta['uri'], '/'))
                            ? substr($meta['uri'], $pos + 1)
                            : $meta['uri'];
                    }
                    else {
                        $filename = 'VOLDEMORE';
                    }

                    $content = '';
                    do {
                        $content .= fread($value, 8192);
                    } while (!feof($value));
                }
                else {
                    throw new Exception('Wadafuck?');
                }

                $body .= '--' . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . $filename . '"' . "\r\n";
                $body .= 'Content-Type: text/plain' . "\r\n" . 'Content-Length: ' . strlen($content) . "\r\n";
                $body .= 'Content-Type: application/octet-stream' . "\r\n\r\n" . $content;
                unset($filename, $content);
            }
            $body .= '--' . $boundary . '--';

            $header  = 'POST ' . $path . ' HTTP/1.1' . "\r\n";
            $header .= 'Host: ' . $this->host . "\r\n";
            $header .= 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n";
            $header .= 'Content-Length: ' . strlen($body) . "\r\n";
            $header .= 'Connection: close' . "\r\n\r\n";
        }
        else {
            $body = http_build_query($params, '', '&');
            $method = $params ? 'POST' : 'GET';
            $header  = $method . ' ' . $path . ' HTTP/1.1' . "\r\n";
            $header .= 'Host: ' . $this->host . "\r\n";
            $header .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
            $header .= 'Content-Length: ' . strlen($body) . "\r\n";
            $header .= 'Connection: close' . "\r\n\r\n";
        }

        $socket = fsockopen($this->scheme . '://' . $this->host, $this->port, $errno, $errstr, $this->connectionTimeout);

        if (!$socket) {
            throw new Exception('Dababug', $errno, $errstr);
        } else {
            socket_set_timeout($socket, $this->transactionTimeout);
            fwrite($socket, $header . $body);
            unset($header, $body);

            $data = '';
            do {
                $data .= fgets($socket, 8192);
                $status = stream_get_meta_data($socket);
            } while (!feof($socket) && !$status['timed_out']);

            fclose($socket);
        }

        return new Response($data);
    }

    function batch()
    {
        return new Batch($this);
    }

    /**
     * Fallbacks section
     */

    function offsetExists($offset)
    {
        return isset($this->fallbacks[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->fallbacks[$offset];
    }

    function offsetSet($offset, $value)
    {
        $this->fallbacks[$offset] = $value;
    }

    function offsetUnset($offset)
    {
        unset($this->fallbacks[$offset]);
    }

    function mergeFallbacks(array $fallbacks)
    {
        $this->fallbacks = array_merge($this->fallbacks, $fallbacks);

        return $this;
    }

    function clearFallbacks()
    {
        $this->fallbacks = array();

        return $this;
    }

    /**
     * Serialization section
     */

    function serialize()
    {
        return serialize(array(
            'api' => $this->api,
            'fallbacks' => $this->fallbacks,
            'connectionTimeout' => $this->connectionTimeout,
            'transactionTimeout' => $this->transactionTimeout,
        ));
    }

    function unserialize($serialized)
    {
        $parameters = unserialize($serialized);
        $this->api = $parameters['api'];
        $this->fallbacks = $parameters['fallbacks'];
        $this->connectionTimeout = $parameters['connectionTimeout'];
        $this->transactionTimeout = $parameters['transactionTimeout'];
        $this->init();
    }
}
