<?php

namespace Mute\Facebook\Bases;

interface Requestable
{
    public function get($path, array $parameters = null);
    public function post($path, array $parameters = null, array $files = null);
    public function put($path, array $parameters = null, array $files = null);
    public function delete($path, array $parameters = null);

    /**
     * @param string|array $query
     */
    public function fql($query, array $parameters = null);
}
