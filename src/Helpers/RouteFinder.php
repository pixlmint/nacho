<?php

namespace Nacho\Helpers;

use Nacho\Contracts\SingletonInterface;
use Nacho\Models\Route;

class RouteFinder implements SingletonInterface
{
    private array $routes;
    private static ?RouteFinder $instance = null;

    public function __construct()
    {
        $this->routes = ConfigurationHelper::getInstance()->getRoutes();
    }

    /**
     * @return RouteFinder|SingletonInterface
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new RouteFinder();
        }

        return self::$instance;
    }

    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoute(string $path): Route
    {
        $route = $this->findRoute($path);
        
        if (!$route) {
            $route = $this->findRoute('/');
        }

        return $route;
    }

    private function findRoute(string $path): ?Route
    {
        if ($path !== '/') {
            $path = substr($path, 1, strlen($path));
        }
        foreach ($this->routes as $route) {
            $tmpRoute = new Route($route);
            if ($tmpRoute->match($path)) {
                return $tmpRoute;
            }
        }

        return null;
    }
}
