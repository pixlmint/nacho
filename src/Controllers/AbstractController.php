<?php

namespace Nacho\Controllers;

use Nacho\Contracts\UserHandlerInterface;
use Nacho\Models\HttpRedirectResponse;
use Nacho\Models\HttpResponse;
use Nacho\Nacho;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

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
            $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/src/Views');
            $this->twig = new Environment($loader);
            $this->twig->addFunction(new TwigFunction('base64_encode', 'base64_encode'));
            $this->twig->addFunction(new TwigFunction('base64_decode', 'base64_decode'));
            $this->twig->addFunction(new TwigFunction('is_array', 'is_array'));
            $this->twig->addFunction(new TwigFunction('is_granted', [$this, 'is_granted']));
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
        $content = $this->getTwig()->render($file, $args);
       
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
