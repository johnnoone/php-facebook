<?php

/**
 * Pricing in a User’s Preferred Currency
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/2012/06/28/pricing-in-a-user-s-preferred-currency/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$access_token = 'USER_TOKEN';
$user_id = 'USER_ID';

/**
 * The process
 **/

$app = new App($app_id, $app_secret);
$result = $app->get($user_id, array(
    'access_token' => $access_token,
    'fields' => 'currency',
));

var_dump($result);
