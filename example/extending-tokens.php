<?php

/**
 * Debugging Access Tokens and Handling Errors
 *
 * In most cases when an app receives an Access Token, it will also receive an
 * expires parameter which indicates when you can expect that token to expire.
 * However, there are several events which can cause an access token to become
 * invalid before its expected expiry time. Examples include: when a user
 * changes their password, when a user de-authorizes an application, or when an
 * App's Secret is reset.
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/docs/howtos/login/debugging-access-tokens/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$access_token = 'TOKEN_TO_DEBUG';

/**
 * The process
 **/

$app = new App($app_id, $app_secret);
$response = $app->get('debug_token', array('input_token' => $access_token));

var_dump($response);
