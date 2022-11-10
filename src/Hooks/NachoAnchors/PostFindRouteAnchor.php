<?php

namespace Nacho\Hooks\NachoAnchors;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\PostFindRoute;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;

class PostFindRouteAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('route', null, true);
    }

    public static function getName(): string
    {
        return 'post_find_route';
    }

    public static function getInterface(): string
    {
        return PostFindRoute::class;
    }

    public function exec(mixed $hook): void
    {
        if (!$hook instanceof PostFindRoute) {
            throw new \Exception('This is not a valid PostFindRoute Hook');
        }

        $this->arguments[0]->setValue($hook->call($this->arguments[0]->getValue()));
    }
}