<?php

namespace Nacho\Helpers;

use Nacho\Contracts\PageHandler;
use Nacho\Models\PicoPage;
use PixlMint\Parsedown\Parsedown;

class MarkdownPageHandler implements PageHandler
{
    private Parsedown $mdParser;
    private PicoPage $page;
    private HookHandler $hookHandler;

    public function __construct(Parsedown $mdParser, HookHandler $hookHandler)
    {
        $this->mdParser = $mdParser;
        $this->hookHandler = $hookHandler;
    }

    public function setPage(PicoPage $page): void
    {
        $this->page = $page;
    }

    public function renderPage(): string
    {
        $page = $this->page;
        if (!isset($page->raw_content)) {
            return '';
        }

        $content = PageManager::prepareFileContent($page->raw_content);
        $content = $this->hookHandler->executeHook('pre_render_markdown', ['raw_content' => $content]);
        $renderedContent = $this->mdParser->parse($content);
        $page->content = $this->hookHandler->executeHook('post_render_markdown', ['content' => $renderedContent]);

        return $page->content;
    }

    public function handleUpdate(string $url, string $newContent, array $newMeta): PicoPage
    {
        return $this->page;
    }

    public function handleDelete(): void
    {
        // TODO: Implement handleDelete() method.
    }
}
