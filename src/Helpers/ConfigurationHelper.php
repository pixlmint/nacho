<?php

namespace Nacho\Helpers;

class ConfigurationHelper
{
    private array $routes;

    public function __construct()
    {
        $config = include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');

        $this->routes = $config['routes'];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}