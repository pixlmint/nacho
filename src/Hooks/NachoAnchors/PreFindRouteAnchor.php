<?php

namespace Nacho\Hooks\NachoAnchors;

use Exception;
use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\PreFindRoute;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;

class PreFindRouteAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('routes', true);
        $this->arguments[] = new HookArgument('path');
    }

    public static function getInterface(): string
    {
        return PreFindRoute::class;
    }

    public static function getName(): string
    {
        return 'pre_find_route';
    }

    public function exec($hook): void
    {
        if (!$hook instanceof PreFindRoute) {
            throw new Exception('This is not a valid PreFindRoute Hook');
        }

        $this->arguments[$this->getIsReturnVar()]->setValue($hook->call($this->arguments[0]->getValue(), $this->arguments[1]->getValue()));
    }
}
