<?php

namespace Mute\Facebook\Bases;

interface RequestHandlerAware
{
    public function getRequestHandler();
    public function setRequestHandler(RequestHandler $requestHandler);
}
