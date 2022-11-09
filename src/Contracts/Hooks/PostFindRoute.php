<?php

namespace Nacho\Contracts\Hooks;

use Nacho\Models\Route;

interface PostFindRoute
{
    public function call(Route $route): Route;
}