<?php

namespace Mute\Facebook\Exception;

use RuntimeException;
use Mute\Facebook\Exception\FacebookException;

/**
 * @link http://curl.haxx.se/libcurl/c/libcurl-errors.html
 */
class CurlException extends RuntimeException implements FacebookException
{
}
