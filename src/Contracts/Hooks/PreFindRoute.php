<?php

namespace Nacho\Contracts\Hooks;

interface PreFindroute
{
    public function call(array $routes, string $requestUrl): array;
}