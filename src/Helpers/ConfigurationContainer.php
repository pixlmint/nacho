<?php

namespace Nacho\Helpers;

use Nacho\Exceptions\ConfigurationDoesNotExistException;

class ConfigurationContainer
{
    private array $config;

    private array $routes = [];
    private array $hooks = [];
    private array $orm = [];
    private array $security = [];
    private array $alternativeContentHandlers = [];
    private array $additionalConfigs = [];

    public function init(array $config = []): void
    {
        $this->config = $config;
        foreach ($this->config as $name => $conf) {
            $this->bootstrapConfig($name);
        }
    }

    public function bootstrapConfig(string $configName): void
    {
        if (key_exists($configName, $this->config)) {
            if (property_exists($this, $configName)) {
                $this->$configName = $this->config[$configName];
            } else {
                $this->additionalConfigs[$configName] = $this->config[$configName];
            }
        }
    }

    public function getAlternativeContentHandlers(): array
    {
        return $this->alternativeContentHandlers;
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
        if (property_exists($this, $configName)) {
            return $this->$configName;
        }
        if (key_exists($configName, $this->additionalConfigs)) {
            return $this->additionalConfigs[$configName];
        }

        throw new ConfigurationDoesNotExistException($configName);
    }
}