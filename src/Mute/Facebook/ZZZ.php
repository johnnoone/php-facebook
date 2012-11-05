<?php

namespace Mute\Facebook;

use Exception;

/**
 * Extends the expiration time of a valid OAuth access token.
 *
 * @see https://developers.facebook.com/roadmap/offline-access-removal/#extend_token
 * @return void
 */
function extendAccessToken($app_id, $app_secret, $access_token)
{

    $args = array(
        'client_id' => $app_id,
        'client_secret' => $app_secret,
        'grant_type' =>'fb_exchange_token',
        'fb_exchange_token' => $access_token,
    );

    $response = $this->raw('/oauth/access_token', $args);
    $query_str = $response->parseQuery();
    if (isset($query_str['access_token'])) {
        return $query_str;
    }
    else {
        return $response->parseJson();
    }
}



/**
 * Parses the cookie set by the official Facebook JavaScript SDK.
 *
 * cookies should be a dictionary-like object mapping cookie names to
 * cookie values.
 *
 * If the user is logged in via Facebook, we return a dictionary with
 * the keys 'uid' and 'access_token'. The former is the user's
 * Facebook ID, and the latter can be used to make authenticated
 * requests to the Graph API. If the user is not logged in, we
 * return None.
 *
 * Download the official Facebook JavaScript SDK at http://github.com/facebook/connect-js/.
 * Read more about Facebook authentication at http://developers.facebook.com/docs/authentication/.
 */
function getUserFromCookie($cookies, $app_id, $app_secret)
{
    $cookieName = 'fbsr_' . $app_id;
    if (empty($_COOKIE[$cookieName])) {
        return;
    }
    $signed_request = $_COOKIE[$cookieName];
    $parsed_request = parseSignedRequest($signed_request, $app_secret);
    if (empty($parsed_request)) {
        return;
    }

    try {
        $result = getAccessTokenFromCode($parsed_request['code'], '', $app_id, $app_secret);
    }
    catch (GraphAPI $e) {
        return;
    }
    $result['uid'] = $parsed_request['user_id'];

    return $result;
}

function authUrl($app_id, $canvas_url, array $perms = null, $state = null)
{
    $url = 'https://www.facebook.com/dialog/oauth?';
    $args = array(
        'client_id' => $app_id,
        'redirect_uri' => $canvas_url
    );
    if ($perms) {
        $args['scope'] = implode(',', $perms);
    }
    if ($state) {
        $args['state'] = $state;
    }

    return $url . http_build_query($args);
}


/**
 * Get an access token from the 'code' returned from an OAuth dialog.
 *
 * Returns a dict containing the user-specific access token and its expiration date (if applicable).
 *
 */
function getAccessTokenFromCode($code, $redirect_uri, $app_id, $app_secret)
{
    # We would use GraphAPI.request() here, except for that the fact
    # that the response is a key-value pair, and not JSON.
    $response = $this->api->raw('/oauth/access_token', array(
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'client_id' => $app_id,
        'client_secret' => $app_secret,
    ));

    $query_str = $response->parseQuery();
    if (isset($query_str['access_token'])) {
        return $query_str;
    }
    return $response->toJson();
}


/**
 * Get the access_token for the app.
 *
 * This token can be used for insights and creating test users.
 *
 * app_id = retrieved from the developer page
 * app_secret = retrieved from the developer page
 *
 * Returns the application access_token.
 */
function getAppAccessToken($app_id, $app_secret)
{
    $response = $this->api->raw('/oauth/access_token', array(
        'grant_type' => 'client_credentials',
        'client_id' => $app_id,
        'client_secret' => $app_secret,
    ));

    return current($response->parseQuery());
}




// example

/**
 * Provides access to the active Facebook user in self.current_user
 *
 * The property is lazy-loaded on first access, using the cookie saved
 * by the Facebook JavaScript SDK to determine the user ID of the active
 * user. See http://developers.facebook.com/docs/authentication/ for
 * more information.
 */
class FBConnect
{
    protected $user;

    public function __construct(User $fbUser)
    {
        $this->fbUser = $user;
    }

    function getUser()
    {
        if (isset($this->user)) {
            return $this->user;
        }

        $this->user = null;
        
        cookie = facebook.get_user_from_cookie(
            self.request.cookies, FACEBOOK_APP_ID, FACEBOOK_APP_SECRET)
        if cookie:
            # Store a local instance of the user data so we don't need
            # a round-trip to Facebook on every request
            user = User.get_by_key_name(cookie["uid"])
            if not user:
                graph = facebook.GraphAPI(cookie["access_token"])
                profile = graph.get_object("me")
                user = User(key_name=str(profile["id"]),
                            id=str(profile["id"]),
                            name=profile["name"],
                            profile_url=profile["link"],
                            access_token=cookie["access_token"])
                user.put()
            elif user.access_token != cookie["access_token"]:
                user.access_token = cookie["access_token"]
                user.put()
            self._current_user = user
        return self._current_user
    }
}
