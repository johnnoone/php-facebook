<?php

/**
 * Accessing Developer Roles for an App via the Graph API and FQL
 *
 * @author Xavier Barbosa
 * @since 13 February, 2013
 * @link https://developers.facebook.com/blog/post/616/
 **/

use Mute\Facebook\App;

/**
 * Default params
 **/

$app_id = "YOUR_APP_ID";
$app_secret = "YOUR_APP_SECRET";

/**
 * The process
 **/

$app = new App($app_id, $app_secret);
$roles = $app->get($app_id . '/roles');
?>
<html>
  <body>
    <div class="fb-login-button">Login with Facebook</div>

    <h3>Existing Roles</h3>
    <p>
      <?php foreach ($roles['data'] as $role) { ?>
        user: <?= $role['user'] ?> /
        role: <?= $role['role'] ?> -
        <a href="javascript:removeRole(<?= $role['user'] ?>)">remove</a><br />
      <?php } ?>
    </p>

    <h3>Add new role</h3>
    <p>
      user: <input type="text" id="user" /> /
      role: <input type="text" id="role" /> -
      <a href="javascript:addRole()">add</a>
    </p>

    <p>Check developer console for API response.</p>

    <div id="fb-root"></div>
    <script src="http://connect.facebook.net/en_US/all.js"></script>
    <script>
    // Define APP_ID
    var APP_ID = <?= json_encode($app_id) ?>;

    FB.init({
      appId  : APP_ID,
      status : true, // check login status
      cookie : true, // enable cookies
      xfbml  : true, // parse XFBML
      oauth  : true // enable OAuth 2.0
    });

    // Function to remove role for a given user
    var removeRole = function(user) {
      FB.api('/' + APP_ID + '/roles', 'delete', { user: user }, logResponse);
    };

    // Function to add new role for a given user
    var addRole = function() {
      user = document.getElementById('user').value;
      role = document.getElementById('role').value;
      FB.api('/' + APP_ID + '/roles', 'post', 
           { user: user, role: role }, logResponse);
    };

    var logResponse = function(response) {
      console.log(response);
    };
   </script>
  </body>
</html>
