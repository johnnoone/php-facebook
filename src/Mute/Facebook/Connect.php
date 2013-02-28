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
        return 'fbsr_' . $this->app->getId();
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
     * @param array $cookies a set of cookies that includes the Facebook cookie
     * @return array|null
     */
    public function getCookie(array $cookies = null)
    {
        if ($cookies === null) {
            $cookies = $_COOKIE;
        }

        if ($data = @$cookies[$this->getCookieName()]) {
            return $this->app->parseSignedRequest($data);
        }
    }

    public function getMetadataCookieName() {
      return 'fbm_' . $this->app->getId();
    }

    /**
    * @param array $cookies a set of cookies that includes the Facebook cookie
    * @return array|null
     */
    public function getMetadataCookie(array $cookies = null)
    {
        if ($cookies === null) {
            $cookies = $_COOKIE;
        }

        if ($data = @$cookies[$this->getMetadataCookieName()]) {
            parse_str($data, $response);
            return $response;
        }
    }
}
