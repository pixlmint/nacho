<?php

namespace Nacho;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Contracts\SingletonInterface;
use Nacho\Helpers\ConfigurationHelper;
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

    private array $anchors;
    private ?Nacho $nacho = null;

    private static ?SingletonInterface $instance = null;

    public function __construct()
    {
        $this->registerAnchor(PreFindRouteAnchor::getName(), new PreFindRouteAnchor());
        $this->registerAnchor(PostFindRouteAnchor::getName(), new PostFindRouteAnchor());
        // $this->anchors = [
        //     self::PRE_CALL_ACTION => [],
        //     self::POST_CALL_ACTION => [],
        //     self::PRE_PRINT_RESPONSE => [],
        // ];
    }

    /**
     * @return SingletonInterface|Core
     */
    public static function getInstance()
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

        $routes = $this->executeHook(PreFindRouteAnchor::getName(), ['routes' => RouteFinder::getInstance()->getRoutes(), 'path' => $path]);
        RouteFinder::getInstance()->setRoutes($routes);

        $route = RouteFinder::getInstance()->getRoute($path);
        $route = $this->executeHook(PostFindRouteAnchor::getName(), ['route' => $route]);
        Request::getInstance()->setRoute($route);

        $content = $this->getContent();

        $this->printContent($content);
    }

    public function executeHook(string $anchorName, array $arguments): mixed
    {
        return $this->anchors[$anchorName]->run($arguments);
    }

    private function loadConfig()
    {
        $config = ConfigurationHelper::getInstance();
        $this->registerConfigHooks($config->getHooks());
    }

    private function registerConfigHooks(array $hooks)
    {
        foreach ($hooks as $hook) {
            $this->registerHook($hook['anchor'], $hook['hook']);
        }
    }

    public function registerHook(string $anchor, string $hook): void
    {
        $this->anchors[$anchor]->addHook($hook);
    }

    public function registerAnchor(string $name, AnchorConfigurationInterface $anchor)
    {
        $this->anchors[$name] = $anchor;
    }

    private function printContent(?string $content)
    {
        if ($content) {
            echo $content;
        } else {
            $route = RouteFinder::getInstance()->getRoute('/');
            $content = $this->getContent($route);
            echo $content;
        }
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
        if (isset($_SERVER['REDIRECT_URL'])) {
            $path = $_SERVER['REDIRECT_URL'];
        } else {
            $path = $_SERVER['REQUEST_URI'];
        }

        if (substr($path, - (strlen($path) === $path)) && $path !== '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }

        return $path;
    }
}
