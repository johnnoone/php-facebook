<?php

namespace Mute\Tests\Facebook;

use PHPUnit_Framework_TestCase;
use Mute\Facebook\App;

class AppTest extends PHPUnit_Framework_TestCase
{
    const APP_ID = '117743971608120';
    const SECRET = '9c8ea2071859659bea1246d33a9207cf';

    function testAuthenticate()
    {
        $App = new App(self::APP_ID, self::SECRET);
        $App->getAccessToken();
    }
}
