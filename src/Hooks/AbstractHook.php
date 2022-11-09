<?php

use Nacho\Nacho;

abstract class AbstractHook
{
    private Nacho $nacho;

    public function __construct(Nacho $nacho)
    {
        $this->nacho = $nacho;
    }
}