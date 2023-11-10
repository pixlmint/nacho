<?php

namespace Nacho\Contracts;

interface RouteFinderInterface
{
    public function getRoute(string $path): RouteInterface;
}