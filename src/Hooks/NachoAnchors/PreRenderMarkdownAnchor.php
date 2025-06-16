<?php

namespace Nacho\Hooks\NachoAnchors;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\PreRenderMarkdownFunction;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;

class PreRenderMarkdownAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('raw_content', true);
    }

    public static function getInterface(): string
    {
        return PreRenderMarkdownFunction::class;
    }

    public static function getName(): string
    {
        return 'pre_render_markdown';
    }

    public function exec(mixed $hook): void
    {
        if (!$hook instanceof PreRenderMarkdownFunction) {
            throw new \Exception('This is not a valid PreRenderMarkdown Hook');
        }

        $this->arguments[0]->setValue($hook->call($this->arguments[0]->getValue()));
    }
}
