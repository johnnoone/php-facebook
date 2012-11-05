<?php

/**
 * Handle expired access tokens
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/2011/05/13/how-to--handle-expired-access-tokens/
 **/

use Mute\Facebook\App;
use Mute\Facebook\Exception\GraphAPIException;

/**
 * Default params
 **/

$app_id = 'YOUR_APP_ID';
$app_secret = 'YOUR_APP_SECRET';
$my_url = 'YOUR_POST_LOGIN_URL';

/**
 * The process
 **/

$app = new App($app_id, $app_secret);

// known valid access token stored in a database
$access_token = "YOUR_STORED_ACCESS_TOKEN";

$code = $_REQUEST["code"];

// If we get a code, it means that we have re-authed the user and can get a valid access_token.
if ($code) {
    $params = $app->getOAuth()->getAccessToken($code);
    $access_token = $params['access_token'];
}

try {
    // Attempt to query the graph:
    $decoded_response = $app->get('me', array(
        'access_token' => $access_token,
    ));

    // success
    echo "success" . $decoded_response['name'];
    echo $access_token;
} catch (GraphAPIException $e) {
    if ($e->getType() == "GraphAPIException") {
        // Retrieving a valid access token.
        $dialog_url = $app->getOAuth()->getCodeURL($my_url);

        echo "<script> top.location.href=" . json_encode($dialog_url) . "</script>";

    }
    else {
        echo "other error has happened";
    }
}

