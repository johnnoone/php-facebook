<?php

namespace Mute\Tests\Facebook;

use PHPUnit_Framework_TestCase;
use Mute\Facebook\GraphApi;

class GraphApiTest extends PHPUnit_Framework_TestCase
{
    function testSerialize()
    {
        $graphApi = new GraphApi('http://example.com');
        $serialized = serialize($graphApi);
        $unserialized = unserialize($serialized);

        $this->assertEquals($graphApi, $unserialized);
    }

    public function testQuery()
    {
        $graphApi = new GraphApi;
        $graphApi['access_token'] = 'AAACEdEose0cBAKiw6Fc05CYcMPF2l0UfrS3QKRQ1qWgnSbYx9jz3TaT0KLVryssBX1whcvffWriZA4tNQWXsLRI7wnWITucZAo8dAaCB2t5c3bl3Tx';
        $response = $graphApi->get('me');
    }
}
