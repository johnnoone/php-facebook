<?php

namespace Mute\Tests\Facebook;

use PHPUnit_Framework_TestCase;
use Mute\Facebook\App,
    Mute\Facebook\GraphApi;

class AppTest extends PHPUnit_Framework_TestCase
{
    function testAuthenticate()
    {
        $graphApi = new GraphApi;
        $App = new App($graphApi, 321796077830415, '88cadf27fc3915f4b5481bb0d8e99bc9');
        $App->getAccessToken();
    }
}
