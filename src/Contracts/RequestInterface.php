<?php

namespace Nacho\Contracts;

use Nacho\Models\ParameterBag;
use Nacho\Models\Route;

interface RequestInterface
{
    public function getBody(): ParameterBag;

    public function setRoute(RouteInterface $route): void;

    public function getRoute(): ?Route;

    public function isMethod(string $method): bool;
}
