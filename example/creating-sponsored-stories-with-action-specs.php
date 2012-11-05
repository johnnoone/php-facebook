<?php

/**
 * Creating Sponsored Stories with Action Specs
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/2012/02/04/creating-sponsored-stories-with-action-specs/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";

/**
 * Promoting story about a user liking a page
 **/

$app = new App($app_id, $app_secret);
$result = $app->post('act_123456789/adgroups', array(
    'campaign_id' => 6004176938239,
    'bid_type' => 1,
    'max_bid' => 30,
    'targeting' => array('countries' => array('US')),
    'creative' => array(
        'type' => 25,
        'action_spec' => json_encode(array('action.type' => 'like', 'page' => 115632122684)),
    ),
    'name' => 'AdGroup Name',
));

var_dump($result);

/**
 * Promoting story about a user liking a specific page post
 **/

$result = $app->post('act_123456789/adgroups', array(
    'campaign_id' => 6004176938239,
    'bid_type' => 1,
    'max_bid' => 30,
    'targeting' => array('countries' => array('US')),
    'creative' => array(
        'type' => 25,
        'action_spec' => json_encode(array('action.type' => 'like', 'post' => 10150420410887685)),
    ),
    'name' => 'AdGroup Name',
));

var_dump($result);
