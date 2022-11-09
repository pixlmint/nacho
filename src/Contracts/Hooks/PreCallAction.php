<?php

namespace Nacho\Contracts\Hooks;

interface PreCallAction
{
    public function call(): void;
}