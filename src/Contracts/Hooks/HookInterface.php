<?php

namespace Nacho\Contracts\Hooks;

interface HookInterface
{
    public function call(mixed $arguments): mixed;

}