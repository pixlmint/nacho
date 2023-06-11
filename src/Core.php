<?php

namespace Nacho;

use Nacho\Contracts\SingletonInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Helpers\ConfigurationHelper;
use Nacho\Helpers\HookHandler;
use Nacho\Hooks\NachoAnchors\PostCallActionAnchor;
use Nacho\Hooks\NachoAnchors\PreCallActionAnchor;
use Nacho\Hooks\NachoAnchors\PrePrintResponseAnchor;
use Nacho\Models\Request;
use Nacho\ORM\RepositoryManager;
use Nacho\Security\JsonUserHandler;
use Nacho\Helpers\RouteFinder;
use Nacho\Hooks\NachoAnchors\PostFindRouteAnchor;
use Nacho\Hooks\NachoAnchors\PreFindRouteAnchor;

class Core implements SingletonInterface
{
    private ?Nacho $nacho = null;

    private static ?SingletonInterface $instance = null;

    public function __construct()
    {
        $hookHandler = HookHandler::getInstance();
        $hookHandler->registerAnchor(PreFindRouteAnchor::getName(), new PreFindRouteAnchor());
        $hookHandler->registerAnchor(PostFindRouteAnchor::getName(), new PostFindRouteAnchor());
        $hookHandler->registerAnchor(PreCallActionAnchor::getName(), new PreCallActionAnchor());
        $hookHandler->registerAnchor(PostCallActionAnchor::getName(), new PostCallActionAnchor());
        $hookHandler->registerAnchor(PrePrintResponseAnchor::getName(), new PrePrintResponseAnchor());
    }

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

    public function run(array $config = []): void
    {
        $this->loadConfig($config);
        $path = $this->getPath();

        $hookHandler = HookHandler::getInstance();

        $routes = $hookHandler->executeHook(PreFindRouteAnchor::getName(), ['routes' => RouteFinder::getInstance()->getRoutes(), 'path' => $path]);
        RouteFinder::getInstance()->setRoutes($routes);

        $route = RouteFinder::getInstance()->getRoute($path);
        $route = $hookHandler->executeHook(PostFindRouteAnchor::getName(), ['route' => $route]);
        Request::getInstance()->setRoute($route);

        $hookHandler->executeHook(PreCallActionAnchor::getName(), []);
        $content = $this->getContent();
        $content = $hookHandler->executeHook(PostCallActionAnchor::getName(), ['returnedResponse' => $content]);

        $content = $hookHandler->executeHook(PrePrintResponseAnchor::getName(), ['response' => $content]);
        RepositoryManager::getInstance()->close();
        $this->printContent($content);
    }

    private function loadConfig(array $config = []): void
    {
        $config = ConfigurationHelper::getInstance($config);
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
        $userHandler = static::getUserHandler();
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

    private static function getUserHandler(): UserHandlerInterface
    {
        $securityConfig = ConfigurationHelper::getInstance()->getSecurity();
        if (key_exists('userHandler', $securityConfig)) {
            $userHandlerStr = $securityConfig['userHandler'];
            $userHandler = new $userHandlerStr();
        } else {
            $userHandler = new JsonUserHandler();
        }

        return $userHandler;
    }

    private function getPath(): string
    {
        $path = $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'];

        if (str_ends_with($path, '/')) {
            $path = substr($path, 0, strlen($path) - 1);
        }

        return $path;
    }
}
