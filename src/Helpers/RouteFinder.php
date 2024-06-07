<?php

namespace Nacho\Helpers;

use Nacho\Contracts\RouteFinderInterface;
use Nacho\Nacho;
use Nacho\Models\Route;

class RouteFinder implements RouteFinderInterface
{
    public function getRoute(string $path): Route
    {
        if ($path === "") {
            $path = "/";
        }
        $route = $this->findRoute($path);
        
        if (!$route) {
            $route = $this->findRoute('/');
        }

        return $route;
    }

    private function findRoute(string $path): ?Route
    {
        $routes = Nacho::$container->get(ConfigurationContainer::class)->getRoutes();
        if ($path !== '/') {
            $path = substr($path, 1, strlen($path));
        }
        foreach ($routes as $route) {
            $tmpRoute = new Route($route);
            if ($tmpRoute->match($path)) {
                return $tmpRoute;
            }
        }

        return null;
    }
}
