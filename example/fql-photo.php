<?php

/**
 * Requesting photo in fql
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/2012/04/11/platform-updates--operation-developer-love/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$access_token = 'USER_TOKEN';

/**
 * Get the place
 **/

$app = new App($app_id, $app_secret);
$result = $app->fql('SELECT place_id FROM photo WHERE object_id = 10150769146166495', array(
    'access_token' => $access_token,
));

var_dump($result);

/**
 * Get the sources
 **/

$app = new App($app_id, $app_secret);
$result = $app->fql('SELECT src, width, height FROM photo_src WHERE photo_id = 10150769146166495 AND width > 480', array(
    'access_token' => $access_token,
));

var_dump($result);
