<?php

namespace Nacho\Hooks;

use Nacho\Contracts\Hooks\HookInterface;
use Nacho\Nacho;

abstract class AbstractHook implements HookInterface
{
    protected ?Nacho $nacho = null;

    public function setNacho(Nacho $nacho): void
    {
        $this->nacho = $nacho;
    }
}