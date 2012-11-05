<?php

/**
 * Logging Users out of your App
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/docs/howtos/login/server-side-logout/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";
$my_url = "YOUR_LOGOUT_URL";

session_start();

/**
 * The process
 **/

$app = new App($app_id, $app_secret);


$code = $_REQUEST["code"];

if($_SESSION['state'] && ($_SESSION['state'] === $_REQUEST['state'])) {
    $params = $app->getOAuth()->getAccessToken($code);
    $_SESSION['access_token'] = $params['access_token'];

    $user = $app->get('me', array(
        'access_token' => $params['access_token'],
    ));
    echo("Hello " . $user->name);

    // Logout button code added below
    echo "<br><a href='logout.php'>Click to log out</a>";
}
else {
    echo("The state does not match. You may be a victim of CSRF.");
    die;
}

$token = $_SESSION["access_token"];
if($token) {
    $result = $app->delete('me/permissions', array(
        'access_token' => $token,
    ));
    if($result) {
       session_destroy();
       echo "User is now logged out.";
    }
} else {
  echo("User already logged out.");
}
