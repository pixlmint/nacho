<?php

namespace Nacho\Hooks\NachoAnchors;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\PostCallFunction;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;

class PostCallActionAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('returnedResponse', true);
    }

    public static function getName(): string
    {
        return 'post_call_action';
    }

    public static function getInterface(): string
    {
        return PostCallFunction::class;
    }

    public function exec(mixed $hook): void
    {
        if (!$hook instanceof PostCallFunction) {
            throw new \Exception('This is not a valid PostFindRoute Hook');
        }

        $this->arguments[0]->setValue($hook->call($this->arguments[0]->getValue()));
    }
}