<?php

namespace Mute\Facebook\Bases;

use Closure;
use Mute\Facebook\Batch;

interface Configurable
{
    /**
     * Returns the local options for the following requests.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Changes the local options.
     *
     * If there is 2 arguments, then it will be considered has an option and
     * his value.
     *
     * @param array|string $name
     * @param mixed $value
     * @return self
     */
    public function setOptions($name, $value = null);

    /**
     * Resets the local options as in construct
     *
     * @return self
     */
    public function resetOptions();
}
