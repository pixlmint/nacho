<?php

namespace Nacho\Hooks\NachoAnchors;

use Exception;
use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\PreRenderTemplate;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;

class PreRenderTemplateAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('template');
        $this->arguments[] = new HookArgument('parameters', true);
    }

    public static function getInterface(): string
    {
        return PreRenderTemplate::class;
    }

    public static function getName(): string
    {
        return 'pre_render_template';
    }

    public function exec(mixed $hook): void
    {
        if (!$hook instanceof PreRenderTemplate) {
            throw new Exception('This is not a valid PreRenderTemplate Hook');
        }

        $this->arguments[$this->getIsReturnVar()]->setValue($hook->call($this->arguments[0]->getValue(), $this->arguments[1]->getValue()));
    }
}
