<?php

namespace Nacho\Hooks\NachoAnchors;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\Hooks\PostRenderMarkdownFunction;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;

class PostRenderMarkdownAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('content', true);
    }

    public static function getInterface(): string
    {
        return PostRenderMarkdownFunction::class;
    }

    public static function getName(): string
    {
        return 'post_render_markdown';
    }

    public function exec(mixed $hook): void
    {
        if (!$hook instanceof PostRenderMarkdownFunction) {
            throw new \Exception('This is not a valid PostRenderMarkdown Hook');
        }

        $this->arguments[0]->setValue($hook->call($this->arguments[0]->getValue()));
    }
}
