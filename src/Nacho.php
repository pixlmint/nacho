<?php

namespace Nacho;

use DateTime;
use DateTimeZone;
use DI\Container;
use Nacho\Contracts\DataHandlerInterface;
use Nacho\Contracts\NachoCoreInterface;
use Nacho\Contracts\PageManagerInterface;
use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\Response;
use Nacho\Contracts\RouteFinderInterface;
use Nacho\Contracts\RouteInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Exceptions\BaseHttpException;
use Nacho\Helpers\AlternativeContentPageHandler;
use Nacho\Helpers\ConfigMerger;
use Nacho\Helpers\ConfigurationContainer;
use Nacho\Helpers\DataHandler;
use Nacho\Helpers\FileHelper;
use Nacho\Helpers\HookHandler;
use Nacho\Helpers\JupyterNotebookHelper;
use Nacho\Helpers\Log\FileLogWriter;
use Nacho\Helpers\Log\Logger;
use Nacho\Helpers\Log\LogWriterInterface;
use Nacho\Helpers\MarkdownPageHandler;
use Nacho\Helpers\MetaHelper;
use Nacho\Helpers\NachoContainerBuilder;
use Nacho\Helpers\PageFinder;
use Nacho\Helpers\PageManager;
use Nacho\Helpers\RouteFinder;
use Nacho\Helpers\TwigTemplateProvider;
use Nacho\Hooks\NachoAnchors\OnRouteNotFoundAnchor;
use Nacho\Hooks\NachoAnchors\PostCallActionAnchor;
use Nacho\Hooks\NachoAnchors\PostFindRouteAnchor;
use Nacho\Hooks\NachoAnchors\PostHandleUpdateAnchor;
use Nacho\Hooks\NachoAnchors\PostRenderMarkdownAnchor;
use Nacho\Hooks\NachoAnchors\PreCallActionAnchor;
use Nacho\Hooks\NachoAnchors\PreFindRouteAnchor;
use Nacho\Hooks\NachoAnchors\PrePrintResponseAnchor;
use Nacho\Hooks\NachoAnchors\PreRenderMarkdownAnchor;
use Nacho\Models\ContainerDefinitionsHolder;
use Nacho\Models\HttpResponse;
use Nacho\Models\Request;
use Nacho\ORM\RepositoryManager;
use Nacho\ORM\RepositoryManagerInterface;
use Nacho\Security\JsonUserHandler;
use Nacho\Security\UserRepository;
use Psr\Log\LoggerInterface;
use function DI\create;
use function DI\factory;
use function DI\get;

class Nacho implements NachoCoreInterface
{
    public static Container $container;
    private bool $configLoaded = false;

    public function init(array|NachoContainerBuilder $containerConfig = []): void
    {
        if (is_array($containerConfig)) {
            $builder = $this->getContainerBuilder();
        } elseif ($containerConfig instanceof NachoContainerBuilder) {
            $builder = $containerConfig;
        } else {
            throw new \Exception('Invalid container config, must be array or ' . NachoContainerBuilder::class . ' instance');
        }
        $builder->addDefinitions($this->getContainerConfig());
        self::$container = $builder->build();

        $this->initAnchors(self::$container->get(HookHandler::class));
    }

    public function getContainerBuilder(): NachoContainerBuilder
    {
        $builder = new NachoContainerBuilder();
        $builder->useAutowiring(true);
        $builder->addDefinitions($this->getContainerConfig());
        return $builder;
    }

    public function run(array $config = []): void
    {
        $this->loadConfig($config);
        $path = $this->getPath();

        $configuration = self::$container->get(ConfigurationContainer::class);

        $hookHandler = self::$container->get(HookHandler::class);
        $hookHandler->registerConfigHooks($configuration->getHooks());

        $routeFinder = self::$container->get(RouteFinderInterface::class);
        $routes = $hookHandler->executeHook(PreFindRouteAnchor::getName(), [
            'routes' => $configuration->getRoutes(),
            'path'   => $path,
        ]);
        $configuration->setRoutes($routes);
        $route = $routeFinder->getRoute($path);
        /** @var RouteInterface $route */
        $route = $hookHandler->executeHook(PostFindRouteAnchor::getName(), ['route' => $route]);
        self::$container->get(LoggerInterface::class)->info("Route for path {$route->getPath()} found: {$route->getController()}::{$route->getFunction()}");

        self::$container->get(RequestInterface::class)->setRoute($route);

        $hookHandler->executeHook(PreCallActionAnchor::getName(), []);
        $content = $this->getContent();
        $content = $hookHandler->executeHook(PostCallActionAnchor::getName(), ['returnedResponse' => $content]);

        $content = $hookHandler->executeHook(PrePrintResponseAnchor::getName(), ['response' => $content]);
        self::$container->get(RepositoryManagerInterface::class)->close();
        self::$container->get(LogWriterInterface::class)->close();
        $this->printContent($content);
    }

    public function loadConfig(array $config = []): void
    {
        if ($this->configLoaded)
            return;
        $configs = [];

        if (!$config) {
            $config = include_once($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        }

        if (key_exists('plugins', $config)) {
            $configs = array_values(self::loadPluginsConfig($config['plugins']));
        }
        unset($config['plugins']);
        $configs[] = $config;

        $configs = ConfigMerger::merge($configs);
        $configContainer = self::$container->get(ConfigurationContainer::class);
        $configContainer->init($configs);
        $this->configLoaded = true;
    }

    private function loadPluginsConfig(array $pluginsConfig): array
    {
        $ret = [];
        foreach ($pluginsConfig as $plugin) {
            if ($this->isPluginEnabled($plugin)) {
                $ret[$plugin['name']] = $plugin['config'];
            }
        }

        return $ret;
    }

    private function isPluginEnabled(array $plugin): bool
    {
        return (key_exists('enabled', $plugin) && $plugin['enabled']) || !key_exists('enabled', $plugin);
    }

    private function printContent(Response $content): void
    {
        $content->send();
    }

    private function getContent(): Response
    {
        /** @var RouteInterface $route */
        $route = self::$container->get(RouteInterface::class);
        $userHandler = self::$container->get(UserHandlerInterface::class);
        if (!$userHandler->isGranted($route->getMinRole())) {
            return new HttpResponse('Unauthorized', 401);
        }
        $controllerClass = $route->getController();
        $controller = self::$container->get($controllerClass);
        $function = $route->getFunction();
        if (!method_exists($controller, $function)) {
            return new HttpResponse("{$function} does not exist in {$controllerClass}", 404);
        }

        try {
            return self::$container->call([$controller, $function]);
        } catch (BaseHttpException $exception) {
            return $this->handleHttpException($exception);
        }
    }

    private function handleHttpException(BaseHttpException $exception): Response
    {
        return new HttpResponse($exception->getMessage(), $exception->getCode());
    }

    public function getPath(): string
    {
        $path = $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'];

        if (str_ends_with($path, '/')) {
            $path = substr($path, 0, strlen($path) - 1);
        }

        return $path;
    }

    private function initAnchors(HookHandler $hookHandler): void
    {
        $hookHandler->registerAnchor(OnRouteNotFoundAnchor::getName(), new OnRouteNotFoundAnchor());
        $hookHandler->registerAnchor(PreFindRouteAnchor::getName(), new PreFindRouteAnchor());
        $hookHandler->registerAnchor(PostFindRouteAnchor::getName(), new PostFindRouteAnchor());
        $hookHandler->registerAnchor(PreCallActionAnchor::getName(), new PreCallActionAnchor());
        $hookHandler->registerAnchor(PostCallActionAnchor::getName(), new PostCallActionAnchor());
        $hookHandler->registerAnchor(PrePrintResponseAnchor::getName(), new PrePrintResponseAnchor());
        $hookHandler->registerAnchor(PostHandleUpdateAnchor::getName(), new PostHandleUpdateAnchor());
        $hookHandler->registerAnchor(PreRenderMarkdownAnchor::getName(), new PreRenderMarkdownAnchor());
        $hookHandler->registerAnchor(PostRenderMarkdownAnchor::getName(), new PostRenderMarkdownAnchor());
    }

    private function getContainerConfig(): ContainerDefinitionsHolder
    {
        return new ContainerDefinitionsHolder(-1, [
            'debug' => factory(function(Container $c) {
                return $c->get(ConfigurationContainer::class)->isDebug();
            }),
            'path' => factory([self::class, 'getPath']),
            'twigTemplatePath' => factory(function() {
                return $_SERVER['DOCUMENT_ROOT'] . '/src/Views';
            }),
            Nacho::class => $this,
            TwigTemplateProvider::class => create(TwigTemplateProvider::class)->constructor(get('twigTemplatePath')),
            DataHandlerInterface::class => create(DataHandler::class),
            UserHandlerInterface::class => create(JsonUserHandler::class),
            PageManagerInterface::class => create(PageManager::class)->constructor(
                get(FileHelper::class),
                get(UserHandlerInterface::class),
                get(LoggerInterface::class),
                get(HookHandler::class),
                get(PageFinder::class),
                get(MarkdownPageHandler::class),
                get(AlternativeContentPageHandler::class),
            ),
            RepositoryManagerInterface::class => create(RepositoryManager::class)->constructor(
                get(DataHandlerInterface::class)
            ),
            RouteInterface::class => factory(function (Container $c) {
                $finder = $c->get(RouteFinderInterface::class);
                return $finder->getRoute($c->get('path'));
            }),
            RequestInterface::class => create(Request::class),
            ConfigurationContainer::class => create(ConfigurationContainer::class),
            RouteFinderInterface::class => create(RouteFinder::class),
            UserRepository::class => create(UserRepository::class),
            MetaHelper::class => create(MetaHelper::class),
            LoggerInterface::class => create(Logger::class)->constructor(
                get(LogWriterInterface::class),
                "{date}\t{level}\t{message}",
            ),
            'logdir' => '/var/www/html/var/log',
            LogWriterInterface::class => factory(function (Container $c) {
                $logDir = $c->get('logdir');
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                $utc = new DateTimeZone('UTC');
                $date = new DateTime('now', $utc);
                $logDir .= DIRECTORY_SEPARATOR . $date->format('Y-m-d') . '.log';
                return new FileLogWriter($logDir);
            }),
            JupyterNotebookHelper::class => create(JupyterNotebookHelper::class)->constructor(
                get(LoggerInterface::class),
                get('debug'),
            ),
        ]);
    }
}
