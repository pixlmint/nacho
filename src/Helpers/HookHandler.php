<?php

namespace Nacho\Helpers;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\SingletonInterface;

class HookHandler implements SingletonInterface
{
    private array $anchors;

    private static ?SingletonInterface $instance = null;

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