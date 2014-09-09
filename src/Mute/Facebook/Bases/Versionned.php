<?php

namespace Mute\Facebook\Bases;

interface Versionned
{
    /**
     * Returns the local version.
     *
     * @return string|null
     */
    public function getVersion();

    /**
     * Returns the versionned version of the current object.
     *
     * @param string|null $version setup the wanted version.
     * @return Versionned returns a versionned clone of current object.
     */
    public function changeVersion($version = null);
}
