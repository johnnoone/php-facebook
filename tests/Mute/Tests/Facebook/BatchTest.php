<?php

namespace Mute\Tests\Facebook;

use PHPUnit_Framework_TestCase;
use Mute\Facebook\App;
use Mute\Facebook\Batch;

/**
 * @link https://developers.facebook.com/docs/reference/api/application/
 */
class BatchTest extends PHPUnit_Framework_TestCase
{
    const APP_ID = '117743971608120';
    const APP_SECRET = '9c8ea2071859659bea1246d33a9207cf';
    const APP_NAMESPACE = 'php-sdk-unit-test';

    function testBatchBuilding()
    {
        $app = new App(self::APP_ID, self::APP_SECRET, self::APP_NAMESPACE);
        $batch = $app->batch()
            ->get(self::APP_ID)
            ->get(self::APP_ID)
            ->get(self::APP_ID)->execute();
        $this->assertEquals(3, count($batch));

        $response = current($batch);
        $this->assertEquals(self::APP_ID, @$response['id']);
        $this->assertEquals(self::APP_NAMESPACE, @$response['namespace']);
    }

    function testClosureBatch()
    {
        $app = new App(self::APP_ID, self::APP_SECRET, self::APP_NAMESPACE);
        $appId = self::APP_ID;
        $batch = $app->batch(function($facebook) use ($appId) {
            $facebook->get($appId);
            $facebook->get($appId);
            $facebook->get($appId);
        });
        $this->assertEquals(3, count($batch));

        $response = current($batch);
        $this->assertEquals(self::APP_ID, @$response['id']);
        $this->assertEquals(self::APP_NAMESPACE, @$response['namespace']);
    }
}
