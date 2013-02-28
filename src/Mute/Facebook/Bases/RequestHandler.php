<?php

namespace Mute\Facebook\Bases;

interface RequestHandler
{
    /**
     * @param string $path
     * @param array|null $parameters
     * @param array|null $files
     * @param array|bool $headers if it is a list, it will be used to send these headers, if if it's not falsey, response will be extended?
     * @param array|null $options passed to the request engine
     * @return array
     */
    public function request($path, array $parameters = null, array $files = null, $headers = null, array $option = null);
}
