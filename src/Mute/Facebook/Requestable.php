<?php

namespace Mute\Facebook;

interface Requestable
{
    public function get($path, array $params = null);
    public function post($path, array $params = null, array $files = null);
    public function put($path, array $params = null, array $files = null);
    public function delete($path, array $params = null);
    public function query($path, $method, array $params = null, array $files = null);
}
