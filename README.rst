Mute Facebook
=============


Implements `Graph API`_ and some of the OAuth facilities to operate with Facebook. See `examples`_ for usage.

This library package requires `PHP 5.3`_ or later.


How to use this library
-----------------------

Simple requests::

    <?php

    $app = new \Mute\Facebook\App(APP_ID, APP_SECRET, APP_NAMESPACE);

    // get name of a user
    $response = $app->get(USER_ID, array('fields' => 'name'));
    echo "user's name is " . $response['name'];

    // or the full response
    $response = $app->get(USER_ID, array('fields' => 'name'), null, true);
    echo "user's name is " . $response['body']['name'];

    // post a photo
    $response = $app->post(USER_ID . '/photo', null, array(
        'source' => PHOTO_FILENAME
    ));

    // get the fresh list friends
    $response = $app->get(USER_ID . '/friends', null, null, array(
        'If-None-Match: ' . PREVIOUS_ETAG,
    ));

By default, app access token will be automatically append. If you need to request with a custom access_token::

    <?php
    $data = $app->get('me', array(
        'access_token' => MY_ACCESS_TOKEN,
    ));
    // or
    $customApp = $app->getAuthenticatedGraphApi(MY_ACCESS_TOKEN);
    $data = $customApp->get('me');

Batched requests::

    <?php
    // only bodies
    $responses = $customApp->batch()
        ->get('me')
        ->get('me/friends', array('limit' => 50))
        ->execute();
    // or
    $responses = $customApp->batch(function($app) {
        $app->get('me');
        $app->get('me/friends', array('limit' => 50));
    });

    // for full responses, you can do
    $responses = $customApp->getAuthenticatedGraphApi(MY_ACCESS_TOKEN)->batch()
        ->get('me')
        ->get('me/friends', array('limit' => 50))
        ->execute(true);
    // or
    $responses = $customApp->batch(function($customApp) {
        $customApp->get('me');
        $customApp->get('me/friends', array('limit' => 50));
    }, true);

Sometimes you need more control of the http request. for this you can manipulate the options::

    <?php
    // fetching a paginated list of friends can very long, set up the timeout to 30 seconds
    $app->setOptions('timeout', 60);
    $friends = $app->get(USER_ID . '/friends', array(
        'offset' => 5000,
        'limit' => 5000,
    ));
    // once finished, you can reset the options
    $app->resetOptions();

Api will hit default graph api version. You may change version::

    <?php
    $app = $app->changeVersion('v2.0');


More
----

For generating the API doc, install apigen_, and then run::

     $ apigen -c apigen.neon

For running unittests, install PHPUnit_, and then run::

    $ phpunit -c tests/phpunit.xml


.. _Graph API: https://developers.facebook.com/docs/reference/api/
.. _examples: https://github.com/johnnoone/php-facebook/tree/master/example
.. _PHP 5.3: http://php.net/releases/5_3_0.php
.. _apigen: apigen.org
.. _PHPUnit: www.phpunit.de