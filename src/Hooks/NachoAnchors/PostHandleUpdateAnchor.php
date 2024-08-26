<?php

namespace Nacho\Hooks\NachoAnchors;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Contracts\Hooks\PostHandleUpdate;

class PostHandleUpdateAnchor extends AbstractAnchor implements AnchorConfigurationInterface {
    public function __construct()
    {
        $this->addArgument('entry', false);
    }

    public static function getName(): string
    {
        return 'post_handle_update';
    }

    public static function getInterface(): string
    {
        return PostHandleUpdate::class;
    }

    public function exec($hook): void
    {
        if (!$hook instanceof PostHandleUpdate) {
            throw new \Exception('This is not a valid PostHandleUpdate Hook');
        }

        $hook->call($this->arguments[0]->getValue());
    }
}
