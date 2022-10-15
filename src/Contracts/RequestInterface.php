<?php

namespace Nacho\Contracts;

interface RequestInterface
{
    public function getBody();

    public function getRoute();
}
