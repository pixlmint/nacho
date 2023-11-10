<?php

namespace Nacho\Helpers;

use Nacho\Contracts\PageHandler;
use Nacho\Models\PicoPage;
use PixlMint\Parsedown\Parsedown;

class MarkdownPageHandler implements PageHandler
{
    private Parsedown $mdParser;
    private PicoPage $page;

    public function __construct(Parsedown $mdParser)
    {
        $this->mdParser = $mdParser;
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
        $page->content = $this->mdParser->parse($content);

        $page->raw_content = str_replace('&amp;', '&', $page->raw_content);
        $page->raw_content = str_replace('&quot;', '"', $page->raw_content);
        $page->raw_content = str_replace('&#039;', '\'', $page->raw_content);
        $page->raw_content = str_replace('&lt;', '<', $page->raw_content);
        $page->raw_content = str_replace('&gt;', '>', $page->raw_content);

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