<?php

namespace Nacho\Helpers;

use Nacho\Exceptions\NachoException;
use Nacho\Hooks\NachoAnchors\PreRenderTemplateAnchor;
use Nacho\Nacho;

class TwigTemplateProvider
{
    private string $twigTemplatePath;
    private mixed $twig = null;

    public function __construct(string $twigTemplatePath)
    {
        $this->twigTemplatePath = $twigTemplatePath;
    }

    public function getEnvironment(): mixed
    {
        $twigClass = "Twig\\Environment";
        $fsLoaderClass = "Twig\\Loader\\FilesystemLoader";
        if (!class_exists($twigClass)) {
            throw new NachoException("Twig package is not installed");
        }

        if (!$this->twig) {
            $loader = new $fsLoaderClass($this->twigTemplatePath);
            $this->twig = new $twigClass($loader);

            Nacho::$container->get(HookHandler::class)->registerAnchor(PreRenderTemplateAnchor::getName(), new PreRenderTemplateAnchor());
        }

        return $this->twig;
    }
}
