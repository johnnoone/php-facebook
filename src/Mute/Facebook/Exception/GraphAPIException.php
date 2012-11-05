<?php

namespace Mute\Facebook\Exception;

/**
 * @link https://developers.facebook.com/docs/reference/api/errors/
 **/

use Exception;
use Mute\Facebook\Exception\FacebookException;

class GraphAPIException extends Exception implements FacebookException
{
    public function __construct(array $data, Exception $previous = null)
    {
        $message = null;
        $code = 0;
        $error_subcode = 0;
        $type = null;
        extract($data);

        $this->error_subcode = $error_subcode;
        $this->type = $type;
        $this->data = $data;

        parent::__construct($message, $code, $previous);
    }

    public function getSubcode()
    {
        return $this->error_subcode;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getData()
    {
        return $this->data;
    }
}
