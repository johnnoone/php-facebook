<?php

namespace Mute\Facebook\Bases;

interface Requestable
{
    /**
     * Handles a GraphAPI GET request.
     *
     * @param string $path
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @param array|bool $headers if it is an array, it will be the headers
     *                          sent to request. response will be extended. If
     *                          it's a bool, response may be extended.
     * @return array|string depends on $headers. an extended response includes
     *                          status, headers and body. A simple response is
     *                          just a body. If body is a response, then it is
     *                          as a json, otherwise raw body will be return.
     */
    public function get($path, array $parameters = null, $headers = null);

    /**
     * Handles a GraphAPI POST request.
     *
     * @param string $path
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @param array $files dictionnary of filenames
     * @param array|bool $headers if it is an array, it will be the headers
     *                          sent to request. response will be extended. If
     *                          it's a bool, response may be extended.
     * @return array|string depends on $headers. an extended response includes
     *                          status, headers and body. A simple response is
     *                          just a body. If body is a response, then it is
     *                          as a json, otherwise raw body will be return.
     */
    public function post($path, array $parameters = null, array $files = null, $headers = null);

    /**
     * Handles a GraphAPI PUT request.
     *
     * @param string $path
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @param array $files dictionnary of filenames
     * @param array|bool $headers if it is an array, it will be the headers
     *                          sent to request. response will be extended. If
     *                          it's a bool, response may be extended.
     * @return array|string depends on $headers. an extended response includes
     *                          status, headers and body. A simple response is
     *                          just a body. If body is a response, then it is
     *                          as a json, otherwise raw body will be return.
     */
    public function put($path, array $parameters = null, array $files = null, $headers = null);

    /**
     * Handles a GraphAPI DELETE request.
     *
     * @param string $path
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @param array|bool $headers if it is an array, it will be the headers
     *                          sent to request. response will be extended. If
     *                          it's a bool, response may be extended.
     * @return array|string depends on $headers. an extended response includes
     *                          status, headers and body. A simple response is
     *                          just a body. If body is a response, then it is
     *                          as a json, otherwise raw body will be return.
     */
    public function delete($path, array $parameters = null, $headers = null);

    /**
     * Handles a single or multi fql query.
     *
     * @param string|array $query or queries
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @param array|bool $headers if it is an array, it will be the headers
     *                          sent to request. response will be extended. If
     *                          it's a bool, response may be extended.
     * @return array|string depends on $headers. an extended response includes
     *                          status, headers and body. A simple response is
     *                          just a body. If body is a response, then it is
     *                          as a json, otherwise raw body will be return.
     */
    public function fql($query, array $parameters = null, $headers = null);
}
