<?php

namespace Mute\Facebook\Bases;

use Closure;
use Mute\Facebook\Batch;

interface Options
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
     * @param array $options 
     * @return self
     */
    public function setOptions(array $options = null);

    /**
     * Resets the local options as in construct
     *
     * @return self
     */
    public function resetOptions();
}
