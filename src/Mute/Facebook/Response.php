<?php

namespace Mute\Facebook;

use Mute\Facebook\Exception\OAuthException;

class Response
{
    protected $data;
    protected $parsed;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function parseJson()
    {
        list($header, $body) = $this->parse();
        $response = json_decode($body, true);

        if (isset($response['error'])) {
            $error = $response['error'];
            if ($error['type'] == 'OAuthException') {
                throw new OAuthException($error['message'], $error['code']);
            }
            throw new Exception($body);
        }

        return $response;
    }

    public function parseQuery()
    {
        list($header, $body) = $this->parse();
        parse_str($body, $params);

        return $params;
    }

    protected function parse()
    {
        if ($this->parsed) {
            return $this->parsed;
        }

        list($header, $body) = explode("\r\n\r\n", $this->data, 2);
        unset($this->data);
        $body = trim($body);
        if (($pos = strpos($header, 'Content-Length:')) !== false) {
            preg_match('/Content-Length:\s*(?P<contentLength>\d+)/', $header, $matches, 0, $pos);
            $body = substr($body, 0, $matches["contentLength"]);
        }
        return $this->parsed = array($header, $body);
    }
}
