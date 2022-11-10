<?php

namespace Nacho\Contracts\Hooks;

interface PreFindRoute
{
    public function call(array $routes, string $requestUrl): array;
}