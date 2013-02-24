<?php

namespace Mute\Facebook\Bases;

use Closure;
use Mute\Facebook\Batch;

interface Batchable
{
    /**
     * Prepares a Batch object, or if $commands is provider,
     * executes the $commands.
     *
     * @param Closure|null $commands if no Closure is given, it will returns
     *                      the Batch instance.
     *                      If a closure is given, the only argument will be
     *                      the current Batch instance.
     * @return Batch|array depends on $commands arguments: Batch if none were
     *                      given, else the results.
     */
    public function batch(Closure $commands = null);
}
