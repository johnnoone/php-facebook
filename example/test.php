<?php


// examples :

$api = new GraphApi;
$response = $api->get('me', array('foo' => "bar"));
$response = $api->post('me');
$responses = $api->batch()
    ->get('foo')
    ->post('bar')
    ->send();
