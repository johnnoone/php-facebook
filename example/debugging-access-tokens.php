<?php

/**
 * Shows how to exchange a short lived access token to a long one.
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/docs/howtos/login/extending-tokens/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$access_token = 'YOUR_PREVIOUS_TOKEN';

/**
 * The process
 **/

$app = new App($app_id, $app_secret);
$new_access_token = $app->getOAuth()->exchangeAccessToken($access_token);
