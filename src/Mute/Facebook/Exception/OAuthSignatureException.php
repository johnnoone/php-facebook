<?php

namespace Mute\Facebook\Exception;

/**
 * @link https://developers.facebook.com/docs/reference/api/errors/
 **/

use Exception;
use Mute\Facebook\Exception\FacebookException;

class OAuthSignatureException extends Exception implements FacebookException
{
}
