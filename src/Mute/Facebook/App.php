<?php

namespace Mute\Facebook;

class App
{
    /**
     * @var GraphApi
     */
    protected $api;

    /**
     * @var int
     */
    public $appId;

    /**
     * @var string
     */
    public $appSecret;

    /**
     * @var string
     */
    protected $accessToken;

    function __construct(GraphApi $api, $app_id, $app_secret, $access_token = null)
    {
        $this->api = $api;
        $this->appId = $app_id;
        $this->appSecret = $app_secret;
        $this->accessToken = $access_token;
    }

    function getAccessToken()
    {
        if (!isset($this->accessToken)) {
            $params = $this->api->raw('/oauth/access_token?' . http_build_query(array(
                'client_id' => $this->appId,
                'client_secret' => $this->appSecret,
                'grant_type' => 'client_credentials',
            ), '', '&'))->parseQuery();
            $this->accessToken = $params['access_token'];
        }

        return $this->accessToken;
    }

    function parseSignedRequest($signed_request)
    {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        if ($data['algorithm'] !== 'HMAC-SHA256') {
            throw new Exception('Unknown algorithm. Expected HMAC-SHA256');
        }

        $signature = base64_decode(strtr($encoded_sig, '-_', '+/'));
        $expected_sig = hash_hmac('sha256', $payload, $this->appSecret, true);
        if ($signature !== $expected_sig) {
            throw new Exception('Bad Signed JSON signature');
        }

        return $data;
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
    function getUserFromCookie()
    {
        $cookieName = 'fbsr_' . $this->appId;
        if (empty($_COOKIE[$cookieName])) {
            return;
        }
        $signed_request = $_COOKIE[$cookieName];
        $parsed_request = $this->app->parseSignedRequest($signed_request);
        if (empty($parsed_request)) {
            return;
        }

        try {
            $result = $this->getAccessTokenFromCode($parsed_request['code'], '');
        }
        catch (GraphAPI $e) {
            return;
        }
        $result['uid'] = $parsed_request['user_id'];

        return $result;
    }

    public function getAccessTokenFromCode($code, $redirect_uri = null)
    {
        $response = $this->api->raw('/oauth/access_token?' . http_build_query(array(
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $redirect_uri,
            'code' => $code,
        ), '', '&'));

        $query_str = $response->parseQuery();
        if (isset($query_str['access_token'])) {
            return $query_str;
        }
        return $response->toJson();
    }

    public function getAccessTokenFromSignedRequest($signed_request)
    {
        $params = $this->parseSignedRequest($signed_request);

        return $params['oauth_token'];
    }
}
