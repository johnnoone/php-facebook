<?php

namespace Mute\Facebook;

/**
 * Implements everything about Javascript SDK
 *
 */
class Connect
{
    /**
     * @var App
     */
    protected $app;

    function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getCookieName()
    {
        return 'fbsr_' . $this->id;
    }

    /**
     * Parses the cookie set Facebook's JavaScript SDK.
     *
     * This method can only be called once per session, as the OAuth code
     * Facebook supplies can only be redeemed once.  Your application
     * must handle cross-request storage of this information; you can no
     * longer call this method multiple times.  (This works out, as the
     * method has to make a call to FB's servers anyway, which you don't
     * want on every call.)
     *
     * @param array $cookie_hash a set of cookies that includes the Facebook cookie.
     * @return the authenticated user's information as a hash, or null.
     *
     */
    public function getUserFromCookies(array $cookies = null)
    {
        if ($cookies === null) {
            $cookies = $_COOKIE;
        }

        if ($data = @$cookies[$this->getCookieName()]) {
            return $this->parseSignedCookie($data);
        }
    }

    public function parseSignedCookie($fb_cookie)
    {
        $components = $this->app->parseSignedRequest($fb_cookie);
        if ($code = @$components["code"]) {
            try {
                $token_info = $this->getAccessToken($code, array('redirect_uri' => ''));
            }
            catch (OAuthTokenRequestException $e) {
                if ($e->getType() == 'GraphAPIException' && strpos($e->getMessage(), 'Code was invalid or expired') !== false) {
                    return null;
                }

                throw $e;
            }

            if ($token_info) {
                return $token_info + $components;
            }

            return $components;
        }

        user_error(
            'Signed cookie didn\'t contain Facebook OAuth code! Components: ' . json_encode($components),
            E_USER_WARNING
        );

        return null;
    }
}
