<?php

/**
 * Writing Questions via Graph API
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/635/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$question = 'What are you doing this weekend?';
$options = json_encode(array('Hiking','Watching a movie','Hacking'));

$page_id = 'YOUR_PAGE_ID';
$app_id = 'YOUR_APP_ID';
$app_secret = 'YOUR_APP_SECRET';
$my_url = 'YOUR_URL';

/**
 * The process
 **/

$app = new App($app_id, $app_secret);

$code = $_REQUEST["code"];

echo '<html><body>';

if (empty($code)) {
    $dialog_url = $app->getOAuth()->getCodeURL($my_url, array('manage_pages'));

    echo "<script> top.location.href=" . json_encode($dialog_url) . "</script>";
}
else {
    $params = $app->getOAuth()->getAccessToken($code);
    $response = $app->get('me/accounts', array(
        'access_token' => $params['access_token'],
    ));
    $accounts = $response['data'];

    // Find the access token for the Page
    $page_access_token = '';
    foreach($accounts as $account) if($account['id'] == $page_id) {
        $page_access_token = $account['access_token'];
        break;
    }

    // Post the question to the Page
    $post_question_url = $app->post($page_id . '/questions', array(
        'question' => $question,
        'options' => $options,
        'allow_new_options' => false,
        'access_token' => $page_access_token,
    ));

    print_r($post_question_url);
}

echo '</body></html>';
