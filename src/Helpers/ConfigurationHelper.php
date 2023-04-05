<?php

namespace Nacho\Helpers;

use Nacho\Contracts\SingletonInterface;
use Nacho\Exceptions\ConfigurationDoesNotExistException;

class ConfigurationHelper implements SingletonInterface
{
    private array $config;

    private array $routes = [];
    private array $hooks = [];
    private array $orm= [];
    private array $security = [];

    private static ?SingletonInterface $instance = null;

    public function __construct()
    {
        $this->config = include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        foreach ($this->config as $name => $conf) {
            $this->bootstrapConfig($name);
        }
    }

    public function bootstrapConfig(string $configName)
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

    public function getSecurity(): array
    {
        return $this->security;
    }

    public function getOrm(): array
    {
        return $this->orm;
    }

    public function getCustomConfig(string $configName): array
    {
        if (!isset($this->$configName)) {
            throw new ConfigurationDoesNotExistException($configName);
        }

        return $this->$configName;
    }
}