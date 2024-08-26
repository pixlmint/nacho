<?php

namespace Nacho\Hooks\NachoAnchors;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\PrePrintResponse;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;

class PrePrintResponseAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('response', true);
    }

    public static function getName(): string
    {
        return 'pre_print_response';
    }

    public static function getInterface(): string
    {
        return PrePrintResponse::class;
    }

    public function exec($hook): void
    {
        if (!$hook instanceof PrePrintResponse) {
            throw new \Exception('This is not a valid PostFindRoute Hook');
        }

        $this->arguments[0]->setValue($hook->call($this->arguments[0]->getValue()));
    }
}
