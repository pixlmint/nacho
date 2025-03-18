<?php

namespace Nacho\Hooks\NachoAnchors;

use Nacho\Contracts\Hooks\OnRouteNotFoundFunction;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;

class OnRouteNotFoundAnchor extends AbstractAnchor
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('path', true);
    }

    public static function getName(): string
    {
        return 'on_route_not_found';
    }

    public static function getInterface(): string
    {
        return OnRouteNotFoundFunction::class;
    }

    public function exec(mixed $hook): void
    {
        if (!$hook instanceof OnRouteNotFoundFunction) {
            throw new \Exception('This is not a valid OnRouteNotFound Hook');
        }

        $this->arguments[0]->setValue($hook->call($this->arguments[0]->getValue()));
    }
}

