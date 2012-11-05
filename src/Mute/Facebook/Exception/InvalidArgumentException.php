<?php

namespace Mute\Facebook\Exception;

use InvalidArgumentException as InvalidArgumentExceptionBase;
use Mute\Facebook\Exception\FacebookException;

class InvalidArgumentException extends InvalidArgumentExceptionBase implements FacebookException
{
}
