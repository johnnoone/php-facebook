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

    public function testOptions()
    {
        $app = new App(self::APP_ID, self::APP_SECRET, self::APP_NAMESPACE);
        $initial = $app->getOptions();
        $changed = $app->setOptions(array('foo' => 'bar'))->getOptions();
        $resetted = $app->resetOptions()->getOptions();
        $this->assertEquals($initial, $resetted);
        $this->assertNotEquals($initial, $changed);
        $this->assertEquals(null, @$initial['foo']);
        $this->assertEquals('bar', @$changed['foo']);
        $changed2 = $app->setOptions('foo', 'bar')->getOptions();
        $this->assertEquals($changed, $changed2);
    }

    // public function testEtag()
    // {
    //     $app = new App(self::APP_ID, self::APP_SECRET, self::APP_NAMESPACE);
    //     $response1 = $app->get(self::APP_ID, null, array('Accept: *'));
    //     $this->assertArrayHasKey('ETag', $response1['headers']);
    //     $headers = array(
    //         'If-None-Match: ' . $response1['headers']['ETag'],
    //     );
    //     $response2 = $app->get(self::APP_ID, null, $headers);
    //     var_dump($response2, $response1['headers']['ETag']);
    //
    //     $response3 = $app->batch()->get(self::APP_ID, null, $headers)->execute(true);
    //     var_dump($response3, $response1['headers']['ETag']);
    // }
}
