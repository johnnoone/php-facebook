<?php

/**
 * Using the signed_request Parameter.
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/docs/howtos/login/signed-request/
 **/

use Mute\Facebook\App;
use Mute\Facebook\Exception\OAuthSignatureException;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$signed_request = 'YOUR_SIGNED_REQUEST';

/**
 * The process
 **/

$app = new App($app_id, $app_secret);

try {
    $data = $app->parseSignedRequest($signed_request);
    echo 'could parse signed request';
    var_dump($data);
}
catch (OAuthSignatureException $e) {
    echo 'could not parse signed request, because' . $e->getMessage();
}
