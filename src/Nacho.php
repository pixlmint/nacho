<?php

namespace Nacho;

use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Exceptions\UserDoesNotExistException;
use Nacho\Helpers\PageManager;

class Nacho
{
    protected RequestInterface $request;
    protected PageManager $pageManager;
    public UserHandlerInterface $userHandler;

    public function __construct(RequestInterface $request, UserHandlerInterface $userHandler)
    {
        $this->request = $request;
        $this->userHandler = $userHandler;
        $this->pageManager = PageManager::getInstance();
    }

    /**
     * @deprecated Use Request::getInstance() instea
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getUserHandler(): UserHandlerInterface
    {
        return $this->userHandler;
    }

    public function isGranted(string $minRight = 'Guest', ?array $user = null)
    {
        try {
            return $this->userHandler->isGranted($minRight, $user);
        } catch (UserDoesNotExistException $e) {
            return true;
        }
    }

    public function getPageManager(): PageManager
    {
        return $this->pageManager;
    }
}
