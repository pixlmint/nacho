<?php

namespace Nacho\Controllers;

use Nacho\Contracts\UserHandlerInterface;
use Nacho\Helpers\HookHandler;
use Nacho\Helpers\TwigTemplateProvider;
use Nacho\Hooks\NachoAnchors\PreRenderTemplateAnchor;
use Nacho\Models\HttpRedirectResponse;
use Nacho\Models\HttpResponse;
use Nacho\Nacho;
use Psr\Log\LoggerInterface;
use Twig\Environment;

abstract class AbstractController
{
    private ?Environment $twig = null;
    private UserHandlerInterface $userHandler;

    public function __construct() {
        $this->userHandler = Nacho::$container->get(UserHandlerInterface::class);
    }

    protected function getTwig(): ?Environment
    {
        if (!$this->twig) {
            $this->twig = Nacho::$container->get(TwigTemplateProvider::class)->getEnvironment();
        }

        return $this->twig;
    }

    protected function getLogger(): LoggerInterface
    {
        return Nacho::$container->get(LoggerInterface::class);
    }

    protected function redirect(string $route, bool $isPermanent = false): HttpRedirectResponse
    {
        return new HttpRedirectResponse($route, $isPermanent);
    }

    protected function json(array $json = [], int $code = 200): HttpResponse
    {
        $response = new HttpResponse(json_encode($json), $code);
        $response->setHeader('content-type', 'application/json');

        return $response;
    }

    protected function render(string $file, array $args = []): HttpResponse
    {
        $twig = $this->getTwig();
        $args = Nacho::$container->get(HookHandler::class)
            ->executeHook(
                PreRenderTemplateAnchor::getName(),
                ['template' => $file, 'parameters' => $args]
            );
        $content = $twig->render($file, $args);
       
        return new HttpResponse($content);
    }

    public function is_granted($role): bool
    {
        return $this->isGranted($role);
    }

    protected function isGranted($role): bool
    {
        return $this->userHandler->isGranted($role);
    }
}
