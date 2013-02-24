<?php

namespace Mute\Facebook\Bases;

interface RequestHandler
{
    /**
     * @param string $path
     * @param array|null $parameters
     * @param array|null $files
     * @param bool $extended should we return only body or the extended response?
     * @return array
     */
    public function request($path, array $parameters = null, array $files = null, $extended = false);
}
