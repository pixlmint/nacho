<?php

namespace Nacho\Helpers;

use Nacho\Contracts\SingletonInterface;

class ConfigurationHelper implements SingletonInterface
{
    private array $config;

    private array $routes = [];
    private array $hooks = [];
    private array $orm= [];

    private static ?SingletonInterface $instance = null;

    public function __construct()
    {
        $this->config = include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        $this->bootstrapRoutes();
        $this->bootstrapConfig('hooks');
        $this->bootstrapConfig('orm');
    }

    private function bootstrapRoutes()
    {
        $this->routes = $this->config['routes'];
    }

    private function bootstrapConfig(string $configName)
    {
        if (key_exists($configName, $this->config)) {
            $this->$configName = $this->config[$configName];
        }
    }

    /**
     * @return SingletonInterface|ConfigurationHelper
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new ConfigurationHelper();
        }

        return self::$instance;
    }

    public function getHooks(): array
    {
        return $this->hooks;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getOrm(): array
    {
        return $this->orm;
    }
}