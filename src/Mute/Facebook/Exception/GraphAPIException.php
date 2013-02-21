<?php

namespace Mute\Facebook\Exception;

/**
 * @link https://developers.facebook.com/docs/reference/api/errors/
 **/

use Exception;
use Mute\Facebook\Exception\FacebookException;

class GraphAPIException extends Exception implements FacebookException
{
    const RECOVERY_AUTHORIZE = 'authorize';
    const RECOVERY_LOGIN = 'login';
    const RECOVERY_PERMISSION = 'permission';
    const RECOVERY_RETRY = 'retry';

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

    /**
     * Follows the facebook recommendations.
     *
     * @return string|null
     */
    public function getRecoveryTactic()
    {
        if (in_array($this->code, array(190, 102))) {
            if (in_array($this->error_subcode, array(459, 464))) {

                return self::RECOVERY_LOGIN;
            }

            return self::RECOVERY_AUTHORIZE;
        }
        elseif (in_array($this->code, array(1, 2, 4, 17))) {

            return self::RECOVERY_RETRY;
        }
        elseif ($this->code == 10 || (200 <= $this->code && $this->code <= 299)) {

            return self::RECOVERY_PERMISSION;
        }
    }
}
