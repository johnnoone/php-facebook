<?php

namespace Mute\Facebook\Exception;


/**
 * @link https://developers.facebook.com/docs/reference/api/errors/
 **/

use Exception;
use Mute\Facebook\Exception\FacebookException;

class HTTPException extends Exception implements FacebookException
{
    public function __construct($http_code, $data, Exception $previous = null)
    {
        $this->data = $data;

        parent::__construct('HTTP error', $http_code, $previous);
    }

    public function getData()
    {
        return $this->data;
    }
}
