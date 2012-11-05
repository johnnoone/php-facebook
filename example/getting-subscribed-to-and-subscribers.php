<?php

/**
 * Getting SubscribedTo and Subscribers via Graph API
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/638/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$my_url = "YOUR_URL";

/**
 * The process
 **/

$app = new App($app_id, $app_secret);
$code = $_REQUEST["code"];

if (empty($code)) {
    $dialog_url = $app->getOAuth()->getCodeURL($my_url, array('user_subscriptions'));

    echo "<script> top.location.href=" . json_encode($dialog_url) . "</script>";
}
else {
    $params = $app->getOAuth()->getAccessToken($code);
    $subscribedto_resp_obj = $app->get('me/subscribedto', array(
        'access_token' => $params['access_token'],
    ));
    $subscribedto = $subscribedto_resp_obj['data'];

    print_r($subscribedto);
}
