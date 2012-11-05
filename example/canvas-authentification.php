<?php

/**
 * Get the id for the connected user.
 *
 * It does not use the cookie of Javascript SDK
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/616/
 **/

use Mute\Facebook\App;
use Mute\Facebook\Exception\GraphAPIException;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";

/**
 * The process
 **/

$app = new App($app_id, $app_secret);

session_start();

$user_id = $_SESSION['user_id'];
$access_token = $_SESSION['access_token'];

// ensure that we are still the current user
$signed_request = isset($_REQUEST['signed_request'])
    ? $app->parseSignedRequest($_REQUEST['signed_request'])
    : null;

if ($signed_request && $signed_request['user_id'] != $user_id) {
    // oups, we are another user, clear the session
    $user_id = null;
}

if (!$user_id) {
    // if a signed request is supplied, then it solely determines who the user is.
    if ($signed_request) {
        if (array_key_exists('user_id', $signed_request)) {
            $user_id = $signed_request['user_id'];

            if ($user_id != $_SESSION['user_id']) {
                session_destroy();
                session_start();
            }

            $user_id = $_SESSION['user_id'] = $signed_request['user_id'];
            $_SESSION['access_token'] = $signed_request['oauth_token'];
            goto finishedAuthentification;
        }
        else {
            session_destroy();
            session_start();
            goto finishedAuthentification;
        }
    }
    else {
        // use access_token to fetch user id if we have a user access_token, or if
        // the cached access token has changed.
        if ($code = $_REQUEST['code']) {
            if ($_REQUEST['state'] == $_SESSION['state']) {
                unset($_SESSION['state']);
                $data = $app->getOAuth()->getAccessToken($code);
                $access_token = $_SESSION['access_token'] = $data['access_token'];
            }
            else {

                die('CSRF state token does not match one provided');
            }
        }

        if ($access_token) {
            try {
                $user_info = $app->get('/me', array(
                    'access_token' => $access_token,
                ));
                $user_id = $_SESSION['user_id'] = $user_info['user_id'];
            } catch (GraphAPIException $e) {
                session_destroy();
                session_start();
            }
        }
        else {
            die('Cannot fetch user without access_token');
        }
    }
}

finishedAuthentification:

echo 'user_id is: ' $user_id;
