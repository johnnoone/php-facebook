<?php

namespace Mute\Facebook\Authentification;

/**
 * Provides access to the active Facebook user in self.current_user
 *
 * The property is lazy-loaded on first access, using the cookie saved
 * by the Facebook JavaScript SDK to determine the user ID of the active
 * user. See http://developers.facebook.com/docs/authentication/ for
 * more information.
 */
class FBConnect extends Base
{
    protected $user;
    protected $facebook;

    public function __construct(User $facebook)
    {
        $this->facebook = $facebook;
    }

    function getUser()
    {
        if (isset($this->currentUser)) {
            return $this->currentUser;
        }

        return $this->currentUser = $this->facebook->getUserFromCookie();
    }
}
