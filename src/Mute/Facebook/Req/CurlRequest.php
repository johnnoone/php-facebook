<?php

namespace Mute\Facebook\Req;

use Mute\Facebook\App;
use Mute\Facebook\Bases\RequestHandler;
use Mute\Facebook\Exception\CurlException;
use Mute\Facebook\Exception\GraphAPIException;
use Mute\Facebook\Exception\HTTPException;
use Mute\Facebook\Exception\InvalidArgumentException;
use Mute\Facebook\Util;


class CurlRequest implements RequestHandler
{
    protected $user_agent = 'Mute/Facebook-2.1 (https://github.com/johnnoone/php-facebook)';

    public function request($path, array $parameters = null, array $files = null, $headers = null, array $options = null)
    {
        list($url, $curlOptions, $extended) = $this->prepare($path, $parameters, $files, $headers, $options);
        return $this->execute($url, $curlOptions, $extended);
    }

    protected function prepare($path, array $parameters = null, array $files = null, $headers = null, array $options = null)
    {
        $parameters = (array) $parameters;

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

        $options = $options
            ? filter_var_array($options, array(
                'connect_timeout' => FILTER_VALIDATE_INT,
                'timeout' => FILTER_VALIDATE_INT,
                'upload_boot' => FILTER_VALIDATE_INT,
            ))
            : array();

        $curlOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $this->user_agent,
            CURLOPT_CONNECTTIMEOUT => isset($options['connect_timeout'])
                ? $options['connect_timeout']
                : 5,
            CURLOPT_TIMEOUT => isset($options['timeout'])
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
            $postFields[$name] = Util::curlFile($file);
            // On *nix it's hopefully not affected by PHP time limit,
            // but on Windows try to send the smallest files you can.
            if ($boost = @$options['upload_boot']) {
                $curlOptions[CURLOPT_TIMEOUT] += filesize($file) / $boost;
            }
        }

        $url = $path;
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

        return array($url, $curlOptions, $extended);
    }

    protected function execute($url, $curlOptions, $extended)
    {
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
}