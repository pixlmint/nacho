<?php

namespace Nacho\Hooks\NachoAnchors;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\PreCallAction;
use Nacho\Hooks\AbstractAnchor;

class PreCallActionAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public static function getName(): string
    {
        return 'pre_call_action';
    }

    public static function getInterface(): string
    {
        return PreCallAction::class;
    }

    public function exec(mixed $hook): void
    {
        if (!$hook instanceof PreCallAction) {
            throw new \Exception('This is not a valid PostFindRoute Hook');
        }

        $hook->call();
    }
}