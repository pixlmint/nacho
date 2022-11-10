<?php

namespace Nacho\Helpers;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\SingletonInterface;
use Nacho\Hooks\NachoAnchors\PostFindRouteAnchor;
use Nacho\Hooks\NachoAnchors\PreFindRouteAnchor;

class HookHandler implements SingletonInterface
{
    private array $anchors;

    private static ?SingletonInterface $instance = null;

    public function __construct()
    {
        $this->registerAnchor(PreFindRouteAnchor::getName(), new PreFindRouteAnchor());
        $this->registerAnchor(PostFindRouteAnchor::getName(), new PostFindRouteAnchor());
        // $this->anchors = [
        //     self::PRE_CALL_ACTION => [],
        //     self::POST_CALL_ACTION => [],
        //     self::PRE_PRINT_RESPONSE => [],
        // ];
    }

    public static function getInstance(): SingletonInterface|HookHandler|null
    {
        if (!self::$instance) {
            self::$instance = new HookHandler();
        }

        return self::$instance;
    }

    public function registerHook(string $anchor, string $hook): void
    {
        $this->anchors[$anchor]->addHook($hook);
    }

    public function executeHook(string $anchorName, array $arguments): mixed
    {
        return $this->anchors[$anchorName]->run($arguments);
    }

    public function registerAnchor(string $name, AnchorConfigurationInterface $anchor)
    {
        $this->anchors[$name] = $anchor;
    }

    public function registerConfigHooks(array $hooks)
    {
        foreach ($hooks as $hook) {
            $this->registerHook($hook['anchor'], $hook['hook']);
        }
    }
}