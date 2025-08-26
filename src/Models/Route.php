<?php

namespace Nacho\Models;

use Nacho\Contracts\RouteInterface;

class Route implements RouteInterface
{
    private string $path;
    private string $controller;
    private string $minRole;
    private string $function;
    private array $variables;
    private array | string $allowedMethods;
    private array $arrRoute;

    public function __construct(array $route)
    {
        $this->arrRoute = $route;
        $this->path = $route['route'];
        $this->controller = $route['controller'];
        $this->function = $route['function'];
        if (!isset($route['variables'])) {
            $route['variables'] = [];
        }
        $this->variables = $route['variables'];
        if (!isset($route['minRole'])) {
            $route['minRole'] = 'Guest';
        }
        $this->minRole = $route['minRole'];
        if ($this->path !== '/') {
            $this->path = substr($this->path, 1, strlen($this->path));
        }
        if (!isset($route['allowedMethods'])) {
            $route['allowedMethods'] = '*';
        }
        $this->allowedMethods = $route['allowedMethods'];
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getMinRole()
    {
        return $this->minRole;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getAllowedMethods(): string | array
    {
        return $this->allowedMethods;
    }

    public function isMethodAllowed(string $method): bool
    {
        if ($this->allowedMethods === '*') {
            return true;
        } elseif (is_string($this->allowedMethods) && strtoupper($this->allowedMethods) === strtoupper($method)) {
            return true;
        } elseif (is_array($this->allowedMethods) && in_array(strtoupper($method), array_map(function(string $method) { return strtoupper($method); }, $this->allowedMethods))) {
            return true;
        }
        return false;
    }

    public function match(string $path)
    {
        if ($path === $this->path) {
            return true;
        }
        $re = '/{[a-zA-Z0-9]*}/m';
        $matches = [];
        preg_match_all($re, $this->path, $matches, PREG_SET_ORDER, 0);
        if (empty($matches)) {
            return false;
        }
        $parts = explode('/', $this->path);
        $pathParts = explode('/', $path);
        if (count($parts) !== count($pathParts)) {
            return false;
        }
        $variables = $this->variables;
        foreach ($matches as $var) {
            $var = $var[0];
            $index = array_search($var, $parts);
            $value = $pathParts[$index];
            $re2 = '/[a-zA-Z0-9]*/m';
            $par = [];
            preg_match_all($re2, $var, $par);
            $par = $par[0][1];
            $variable = [];
            foreach ($variables as $tmp) {
                if ($tmp['variable'] === $par && isset($tmp['options'])) {
                    $variable = $tmp;
                    break;
                }
            }
            if (!$variable) {
                $this->$par = $value;
            } else {
                $options = $variable['options'];
                if (is_bool(array_search($value, $options))) {
                    return false;
                }
                $this->$par = $value;
            }
        }

        return true;
    }

    private function setVariable(string $key, string $value)
    {
        $this->$key = $value;

        return $this;
    }

    private function getPathVariables(string $path)
    {
    }
}
