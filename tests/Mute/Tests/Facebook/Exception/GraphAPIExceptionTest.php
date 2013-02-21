<?php

namespace Mute\Tests\Facebook\Exception;

use PHPUnit_Framework_TestCase;
use Mute\Facebook\Exception\GraphAPIException;

/**
 * @link https://developers.facebook.com/docs/reference/api/application/
 */
class GraphAPIExceptionTest extends PHPUnit_Framework_TestCase
{
    public static function provider()
    {
        return array(
          array(array('code' => 190, 'error_subcode' => null), GraphAPIException::RECOVERY_AUTHORIZE),
          array(array('code' => 102, 'error_subcode' => null), GraphAPIException::RECOVERY_AUTHORIZE),
          array(array('code' => 190, 'error_subcode' => 459), GraphAPIException::RECOVERY_LOGIN),
          array(array('code' => 102, 'error_subcode' => 459), GraphAPIException::RECOVERY_LOGIN),
          array(array('code' => 190, 'error_subcode' => 464), GraphAPIException::RECOVERY_LOGIN),
          array(array('code' => 102, 'error_subcode' => 464), GraphAPIException::RECOVERY_LOGIN),
          array(array('code' => 1, 'error_subcode' => null), GraphAPIException::RECOVERY_RETRY),
          array(array('code' => 2, 'error_subcode' => null), GraphAPIException::RECOVERY_RETRY),
          array(array('code' => 4, 'error_subcode' => null), GraphAPIException::RECOVERY_RETRY),
          array(array('code' => 17, 'error_subcode' => null), GraphAPIException::RECOVERY_RETRY),
          array(array('code' => 10, 'error_subcode' => null), GraphAPIException::RECOVERY_PERMISSION),
          array(array('code' => 200, 'error_subcode' => null), GraphAPIException::RECOVERY_PERMISSION),
          array(array('code' => 242, 'error_subcode' => null), GraphAPIException::RECOVERY_PERMISSION),
          array(array('code' => 299, 'error_subcode' => null), GraphAPIException::RECOVERY_PERMISSION),
          array(array('code' => 42, 'error_subcode' => null), null),
        );
    }

    /**
     * @dataProvider provider
     */
    public function testRecoveryTactics($error, $tactic)
    {
        $exception = new GraphAPIException($error);

        $this->assertEquals($tactic, $exception->getRecoveryTactic());
    }
}
