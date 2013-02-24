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
     * @return array|string parsed json or raw body
     */
    public function get($path, array $parameters = null);

    /**
     * Handles a GraphAPI POST request.
     *
     * @param string $path
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @param array $files dictionnary of filenames
     * @return array|string parsed json or raw body
     */
    public function post($path, array $parameters = null, array $files = null);

    /**
     * Handles a GraphAPI PUT request.
     *
     * @param string $path
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @param array $files dictionnary of filenames
     * @return array|string parsed json or raw body
     */
    public function put($path, array $parameters = null, array $files = null);

    /**
     * Handles a GraphAPI DELETE request.
     *
     * @param string $path
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @return array|string parsed json or raw body
     */
    public function delete($path, array $parameters = null);

    /**
     * Handles a single or multi fql query.
     *
     * @param string|array $query or queries
     * @param array $parameters missing keys (access_token, method...)
     *                          may be filled by self
     * @return array parsed json
     */
    public function fql($query, array $parameters = null);
}
