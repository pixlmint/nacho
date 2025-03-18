<?php

namespace Nacho\Contracts\Hooks;

use Nacho\Models\Route;

interface OnRouteNotFoundFunction
{
    public function call(string $path): Route;
}
