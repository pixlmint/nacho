<?php

namespace Nacho\Helpers;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\HookInterface;
use Nacho\Contracts\SingletonInterface;
use Nacho\Nacho;

class HookHandler
{
    /** @var array|AnchorConfigurationInterface $anchors */
    private $anchors;

    public function registerHook(string $anchor, string $hook): void
    {
        $this->anchors[$anchor]->addHook($hook);
    }

    public function executeHook(string $anchorName, array $arguments)
    {
        return $this->anchors[$anchorName]->run($arguments);
    }

    /**
    * @return array|AnchorConfigurationInterface
    */
    public function getAnchors()
    {
        return $this->anchors;
    }

    public function registerAnchor(string $name, AnchorConfigurationInterface $anchor): void
    {
        Nacho::$container->set($name, $anchor);
        $this->anchors[$name] = $anchor;
    }

    public function registerConfigHooks(array $hooks): void
    {
        foreach ($hooks as $hook) {
            $this->registerHook($hook['anchor'], $hook['hook']);
        }
    }
}
