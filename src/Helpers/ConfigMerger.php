<?php

namespace Nacho\Helpers;

class ConfigMerger
{
    public static function merge(array $configurations): array
    {
        $routes = [];
        $hooks = [];
        foreach ($configurations as $config) {
            if (key_exists('routes', $config)) {
                $routes = array_merge($routes, $config['routes']);
                unset($config['routes']);
            }
            if (key_exists('hooks', $config)) {
                $hooks = array_merge($hooks, $config['hooks']);
                unset($config['hooks']);
            }
        }
        $routes = self::mergeRoutes($routes);

        $configurations = array_replace_recursive(...$configurations);
        $configurations['routes'] = $routes;
        $configurations['hooks'] = $hooks;

        return $configurations;
    }

    private static function mergeRoutes(array $routes)
    {
        $out = [];
        foreach ($routes as $route) {
            $out[$route['route']] = $route;
        }

        return array_values($out);
    }
}
