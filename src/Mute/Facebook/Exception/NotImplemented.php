<?php

namespace Mute\Facebook\Exception;

use RuntimeException;
use Mute\Facebook\Exception\FacebookException;

class NotImplemented extends RuntimeException implements FacebookException
{
}
