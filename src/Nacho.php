<?php

namespace Nacho;

use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Exceptions\UserDoesNotExistException;
use Nacho\Helpers\MarkdownHelper;
use Nacho\Models\PicoPage;

class Nacho
{
    protected RequestInterface $request;
    protected MarkdownHelper $markdownHelper;
    public UserHandlerInterface $userHandler;

    public function __construct(RequestInterface $request, UserHandlerInterface $userHandler)
    {
        $this->request = $request;
        $this->userHandler = $userHandler;
        $this->markdownHelper = new MarkdownHelper();
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getUserHandler(): UserHandlerInterface
    {
        return $this->userHandler;
    }

    /**
     * @return void
     * @deprecated Use `$nacho->getMarkdownHelper()->clearPages()` instead
     */
    public function clearPages(): void
    {
        $this->markdownHelper->clearPages();
    }

    public function isGranted(string $minRight = 'Guest', ?array $user = null)
    {
        try {
            return $this->userHandler->isGranted($minRight, $user);
        } catch (UserDoesNotExistException $e) {
            return true;
        }
    }

    /**
     * @return array
     * @deprecated Use `$nacho->getMarkdownHelper()->getPages()` instead
     */
    public function getPages(): array
    {
        return $this->markdownHelper->getPages();
    }

    /**
     * @param string $url
     * @return ?PicoPage
     * @deprecated Use `$nacho->getMarkdownHelper()->getPage($url)` instead
     */
    public function getPage(string $url): ?PicoPage
    {
        return $this->markdownHelper->getPage($url);
    }

    public function getMarkdownHelper(): MarkdownHelper
    {
        return $this->markdownHelper;
    }
}
