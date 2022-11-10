<?php

namespace Nacho;

use Nacho\Contracts\SingletonInterface;
use Nacho\Helpers\ConfigurationHelper;
use Nacho\Helpers\HookHandler;
use Nacho\Models\Request;
use Nacho\Security\JsonUserHandler;
use Nacho\Nacho;
use Nacho\Helpers\RouteFinder;
use Nacho\Hooks\NachoAnchors\PostFindRouteAnchor;
use Nacho\Hooks\NachoAnchors\PreFindRouteAnchor;

class Core implements SingletonInterface
{
    const PRE_CALL_ACTION = 'pre_call_action';
    const POST_CALL_ACTION = 'post_call_action';
    const PRE_PRINT_RESPONSE = 'pre_print_response';

    private ?Nacho $nacho = null;

    private static ?SingletonInterface $instance = null;

    /**
     * @return SingletonInterface|Core|null
     */
    public static function getInstance(): SingletonInterface|Core|null
    {
        if (!self::$instance) {
            self::$instance = new Core();
        }

        return self::$instance;
    }

    public function run()
    {
        $this->loadConfig();
        $path = $this->getPath();

        $hookHandler = HookHandler::getInstance();

        $routes = $hookHandler->executeHook(PreFindRouteAnchor::getName(), ['routes' => RouteFinder::getInstance()->getRoutes(), 'path' => $path]);
        RouteFinder::getInstance()->setRoutes($routes);

        $route = RouteFinder::getInstance()->getRoute($path);
        $route = $hookHandler->executeHook(PostFindRouteAnchor::getName(), ['route' => $route]);
        Request::getInstance()->setRoute($route);

        $content = $this->getContent();

        $this->printContent($content);
    }

    private function loadConfig()
    {
        $config = ConfigurationHelper::getInstance();
        HookHandler::getInstance()->registerConfigHooks($config->getHooks());
    }

    private function printContent(?string $content)
    {
        if (!$content) {
            $route = RouteFinder::getInstance()->getRoute('/');
            $content = $this->getContent($route);
        }
        echo $content;
    }

    private function getContent(): string
    {
        $route = Request::getInstance()->getRoute();
        $userHandler = new JsonUserHandler();
        $this->nacho = new Nacho(Request::getInstance(), $userHandler);
        if (!$this->nacho->isGranted($route->getMinRole())) {
            header('Http/1.1 401');
            die();
        }
        $controllerDir = $route->getController();
        $cnt = new $controllerDir($this->nacho);
        $function = $route->getFunction();
        if (!method_exists($cnt, $function)) {
            header('Http/1.1 404');
            return "${function} does not exist in ${controllerDir}";
        }

        return $cnt->$function(Request::getInstance());
    }

    private function getPath(): string
    {
        $path = $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'];

        if (substr($path, - (strlen($path) === $path)) && $path !== '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }

        return $path;
    }
}
