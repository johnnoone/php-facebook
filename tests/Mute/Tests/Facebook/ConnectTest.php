<?php

namespace Mute\Tests\Facebook;

use PHPUnit_Framework_TestCase;
use Mute\Facebook\App;

/**
 * @link https://developers.facebook.com/docs/reference/api/application/
 */
class ConnectTest extends PHPUnit_Framework_TestCase
{
    const APP_ID = '117743971608120';
    const APP_SECRET = '9c8ea2071859659bea1246d33a9207cf';

    function testCookies()
    {
        $app = new App(self::APP_ID, self::APP_SECRET);
        $connect = $app->getConnect();

        $data = array(
            'foo' => 'bar',
        );

        $meta = array(
            'baz' => 'qux',
        );

        $cookies = array(
            $connect->getCookieName() => $app->makeSignedRequest($data),
            $connect->getMetadataCookieName() => http_build_query($meta, '', '&'),
        );

        $response = $connect->getCookie($cookies);
        $this->assertEquals($data['foo'], $response['foo']);

        $response2 = $connect->getMetadataCookie($cookies);
        $this->assertEquals($meta['baz'], $response2['baz']);
    }
}
