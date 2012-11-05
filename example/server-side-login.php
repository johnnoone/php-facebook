<?php

/**
 * Login for Server-side Apps.
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/docs/howtos/login/server-side-re-auth/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$my_url = "YOUR_URL";

session_start();

/**
 * The process
 **/

$app = new App($app_id, $app_secret);


$code = $_REQUEST["code"];

if (empty($code)) {
    $_SESSION['state'] = md5(uniqid(rand(), true));
    $_SESSION['nonce'] = md5(uniqid(rand(), TRUE)); // New code to generate auth_nonce
    $dialog_url = $app->getOAuth()->getCodeURL($my_url, array('user_birthday', 'read_stream'), $_SESSION['state']);

    echo "<script> top.location.href=" . json_encode($dialog_url) . "</script>";
    die;
}

if($_SESSION['state'] && ($_SESSION['state'] === $_REQUEST['state'])) {
    $params = $app->getOAuth()->getAccessToken($code);
    $_SESSION['access_token'] = $params['access_token'];

    $user = $app->get('me', array(
        'access_token' => $params['access_token'],
    ));
    echo("Hello " . $user->name);
}
else {
    echo("The state does not match. You may be a victim of CSRF.");
}
