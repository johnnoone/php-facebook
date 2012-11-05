<?php

namespace Mute\Facebook\Bases;

interface RequestHandler
{
    public function request($path, array $parameters = null, array $files = null);
}
