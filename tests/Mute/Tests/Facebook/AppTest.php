<?php

namespace Mute\Tests\Facebook;

use PHPUnit_Framework_TestCase;
use Mute\Facebook\App;

/**
 * @link https://developers.facebook.com/docs/reference/api/application/
 */
class AppTest extends PHPUnit_Framework_TestCase
{
    const APP_ID = '117743971608120';
    const APP_SECRET = '9c8ea2071859659bea1246d33a9207cf';
    const APP_NAMESPACE = 'php-sdk-unit-test';

    function testApp()
    {
        $app = new App(self::APP_ID, self::APP_SECRET, self::APP_NAMESPACE);
        $response = $app->get(self::APP_ID);
        $this->assertEquals(self::APP_ID, @$response['id']);
        $this->assertEquals(self::APP_NAMESPACE, @$response['namespace']);
    }

    public function testSignedRequest()
    {
        $app = new App(self::APP_ID, self::APP_SECRET, self::APP_NAMESPACE);
        $signed_request = $app->makeSignedRequest(array(
            'foo' => 'bar',
        ));
        $response = $app->parseSignedRequest($signed_request);
        $this->assertEquals('bar', @$response['foo']);
    }

    public function testExtended()
    {
        $app = new App(self::APP_ID, self::APP_SECRET, self::APP_NAMESPACE);
        $response = $app->get(self::APP_ID, null, true);
        $this->assertEquals(self::APP_ID, @$response['body']['id']);
        $this->assertEquals(self::APP_NAMESPACE, @$response['body']['namespace']);
    }

    public function testHeaders()
    {
        $app = new App(self::APP_ID, self::APP_SECRET, self::APP_NAMESPACE);
        $response = $app->get(self::APP_ID, null, array('Accept: *'));
        $this->assertEquals(self::APP_ID, @$response['body']['id']);
        $this->assertEquals(self::APP_NAMESPACE, @$response['body']['namespace']);
    }

    // public function testEtag()
    // {
    //     $this->assertEquals($response1['body'], $response2['body']);
    //     $this->assertArrayHasKey('ETag', $response1['headers']);
    //     $headers = array(
    //         'If-None-Match: ' . $response1['headers']['ETag'],
    //     );
    //     $response3 = $app->get(self::APP_ID, null, $headers);
    //     $this->assertEquals(self::APP_ID, @$response['body']['id']);
    //     $this->assertEquals(self::APP_NAMESPACE, @$response['body']['namespace']);
    // }
}
