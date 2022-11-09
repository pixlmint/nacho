<?php

namespace Nacho\Contracts\Hooks;

interface PostCallFunction
{
    public function call(mixed $returnedResponse): mixed;
}