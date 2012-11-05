<?php

/**
 * New Page APIs
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/2012/03/14/new-page-apis/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";

/**
 * Reading Milestones
 **/

$app = new App($app_id, $app_secret);
$result = $app->get('PAGE_ID/milestones');

var_dump($result);

/**
 * Creating a Milestone
 **/

$result = $app->post('PAGE_ID/milestones', array(
    'title' => 'Example Title',
    'description' = >'Description',
    'start_time' => 1329417443,
));

var_dump($result);

/**
 * Deleting a Milestone
 **/

$result = $app->delete('PAGE_ID/milestones');

var_dump($result);

/**
 * Editing a Page's Attributes
 **/

$result = $app->post('PAGE_ID/milestones', array(
    'about' => 'About Text',
    'phone' => '415-448-4444',
    'description' => 'Description',
    'general_info' => 'Info',
    'website' => 'http://example.com',
));

var_dump($result);

/**
 * Setting Page's cover photo
 **/

$result = $app->post('PAGE_ID/milestones', array(
    'cover' => 1232343,
    'offset_y' => 30,
    'no_feed_story' => false,
));

var_dump($result);

/**
 * Reading Page's cover photo
 **/

$result = $app->fql('SELECT pic_cover from page where page_id = PAGE_ID');

var_dump($result);

/**
 * Reading Page Apps
 **/

$result = $app->fql('SELECT name, link FROM profile_view WHERE profile_id = PAGE_ID');

var_dump($result);

/**
 * Updating a Page App's Image
 **/

$result = $app->post('PAGE_ID/tabs/app_<APP_ID>', array(
    'custom_image_url' => 'http://example.com/image.jpg',
));

var_dump($result);

/**
 * Uploading a Page App's Image
 **/

$result = $app->post('PAGE_ID/tabs/app_<APP_ID>', null, array(
    'custom_image' => realpath('tab_image.png'),
));

var_dump($result);

/**
 * Reading Messages
 **/

$result = $app->get('PAGE_ID/PAGE_ID/conversations');

var_dump($result);

/**
 * Replying To a Message
 **/

$result = $app->post('THREAD_ID/messages', array(
    'message' => 'A Reply',
));

var_dump($result);

/**
 * Hidding a Page post
 **/

$result = $app->post('<POST_ID>', array(
    'is_hidden' => true,
));

var_dump($result);

