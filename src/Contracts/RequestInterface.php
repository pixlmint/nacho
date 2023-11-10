<?php

namespace Nacho\Contracts;

interface RequestInterface
{
    public function getBody();

    public function setRoute(RouteInterface $route);

    public function getRoute();
}
