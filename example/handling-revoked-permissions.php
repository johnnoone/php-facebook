<?php

/**
 * The Permissions that are requested from a User by an App may not be fully
 * granted, or may not remain constant - a user can choose to not grant some
 * Permissions and can revoke these Permissions afterwards through their
 * Facebook account settings. In order to provide a positive user experience,
 * Apps should be built to handle these situations.
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/docs/howtos/login/handling-revoked-permissions/
 **/

use Mute\Facebook\App;
use Mute\Facebook\Exception\GraphAPIException;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$access_token = 'USER_ACCESS_TOKEN';
$user_id = 'USER_ID';

/**
 * Step 1. Detecting Granted Permissions
 **/

$app = new App($app_id, $app_secret);
$permissions = $app->get($user_id . '/permissions', array(
    'access_token' => $access_token,
));

var_dump($permissions);

/**
 * Step 2. Failing Gracefully When Encountering Missing Permissions
 **/

try {
    $app->post('me/socialhiking:hike', array(
        'access_token' => $access_token,
        'hike' => 'http://samples.ogp.me/338293766265387',
    ));
} catch (GraphAPIException $e) {
    var_dump($e->getData);
}
