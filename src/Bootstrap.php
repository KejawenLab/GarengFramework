<?php

namespace KejawenLab\Framework\GarengFramework;

use KejawenLab\Framework\GarengFramework\Api\Client;
use KejawenLab\Framework\GarengFramework\Api\ClientInterface;
use KejawenLab\Framework\GarengFramework\Configuration\Configuration;
use KejawenLab\Framework\GarengFramework\Controller\ControllerResolver;
use KejawenLab\Framework\GarengFramework\EventListener\RegisterListenerMiddleware;
use KejawenLab\Framework\GarengFramework\Http\Kernel;
use KejawenLab\Framework\GarengFramework\Http\RouteMiddleware;
use KejawenLab\Framework\GarengFramework\Middleware\MiddlewareBuilder;
use KejawenLab\Framework\GarengFramework\Middleware\MiddlewareStack;
use KejawenLab\Framework\GarengFramework\Twig\TwigExtensionMiddleware;
use KejawenLab\Framework\GarengFramework\Twig\TwigTemplateEngine;
use Pimple\Container;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Muhamad Surya Iksanudin <surya.kejawen@gmail.com>
 */
abstract class Bootstrap extends Container
{
    /**
     * @var bool
     */
    private $booted = false;

    /**
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @return string
     */
    abstract protected function projectDir();

    /**
     * @param CacheItemPoolInterface|null $cachePool
     * @param array                       $values
     */
    public function __construct(CacheItemPoolInterface $cachePool = null, array $values = array())
    {
        parent::__construct($values);
        $this->cachePool = $cachePool;

        $this['project_dir'] = $this->projectDir();
    }

    /**
     * @param string $configDir
     * @param string $environment
     */
    public function boot($configDir = 'configs', $environment = 'dev')
    {
        if ($this->booted) {
            throw new \RuntimeException(sprintf('Application is booted.'));
        }

        $this['environment'] = strtolower($environment);

        $cachePath = sprintf('%s/caches', $this->projectDir());
        if (!$this->cachePool) {
            if ('prod' === $this['environment']) {
                $this->cachePool = new ApcuAdapter();
            } else {
                $this->cachePool = new FilesystemAdapter('client_platform', 3600, $cachePath);
            }
        }

        $cachePool = $this->cachePool;
        $this['internal.cache_handler'] = function () use ($cachePool) {
            return $cachePool;
        };

        $this->processConfiguration($configDir);
        $this->buildContainer();

        $this['internal.template'] = function ($container) use ($cachePath) {
            $templateClass = $container['template']['engine'];
            $viewPath = sprintf('%s/%s', $container['project_dir'], $container['template']['path']);

            $cache = false;
            if ('prod' === $container['environment']) {
                $cache = $cachePath;
            }

            if ($templateClass) {
                $templateEngine = new $templateClass($viewPath, $cache);
            } else {
                $templateEngine = new TwigTemplateEngine($viewPath, $cache);
            }

            return $templateEngine;
        };

        $this->booted = true;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        /** @var MiddlewareBuilder $middlewareBuilder */
        $middlewareBuilder = $this['internal.middleware_builder'];
        $middlewareBuilder->addMiddleware(RouteMiddleware::class, [], 2047);
        $middlewareBuilder->addMiddleware(RegisterListenerMiddleware::class, [], 2047);
        $middlewareBuilder->addMiddleware(TwigExtensionMiddleware::class, [], 2045);

        foreach ($this['middlewares'] as $middleware) {
            $middlewareBuilder->addMiddleware($middleware['class'], $middleware['parameters'], $middleware['priority']);
        }

        /** @var MiddlewareStack $middlewareStack */
        $middlewareStack = $this['internal.middleware_stack'];
        foreach ($middlewareBuilder->getMiddlewares() as $middleware) {
            call_user_func_array([$middlewareStack, 'push'], $middleware);
        }

        $app = $middlewareStack->resolve($this['internal.kernel']);
        $response = $app->handle($request);

        return $response->send();
    }

    private function buildContainer()
    {
        $this['internal.http_client'] = function ($container) {
            $clientClass = $container['http']['client'];
            if ($clientClass) {
                /** @var ClientInterface $httpClient */
                $httpClient = new $clientClass($container['internal.session_storage'], $container['api']['base_url'], $container['api']['api_key'], $container['api']['param_key']);
            } else {
                /** @var ClientInterface $httpClient */
                $httpClient = new Client($container['internal.session_storage'], $container['api']['base_url'], $container['api']['api_key'], $container['api']['param_key']);
            }

            $httpClient->setMethodHeaders('get', $container['http']['get']);
            $httpClient->setMethodHeaders('post', $container['http']['post']);
            $httpClient->setMethodHeaders('put', $container['http']['put']);
            $httpClient->setMethodHeaders('patch', $container['http']['patch']);
            $httpClient->setMethodHeaders('delete', $container['http']['delete']);

            return $httpClient;
        };

        $this['internal.controller_resolver'] = function ($container) {
            return new ControllerResolver($container['internal.url_matcher']);
        };

        $this['internal.event_dispatcher'] = function () {
            return new EventDispatcher();
        };

        $this['internal.kernel'] = function ($container) {
            return new Kernel($container['internal.event_dispatcher']);
        };

        $this['internal.middleware_builder'] = function () {
            return new MiddlewareBuilder();
        };

        $this['internal.middleware_stack'] = function ($container) {
            return new MiddlewareStack($container);
        };

        $this['internal.request_context'] = function () {
            return new RequestContext();
        };

        $this['internal.route_collection'] = function () {
            return new RouteCollection();
        };

        $this['internal.url_matcher'] = function ($container) {
            return new UrlMatcher(
                $container['internal.route_collection'],
                $container['internal.request_context']
            );
        };

        $this['internal.session_storage'] = function ($container) {
            $storage = new NativeSessionStorage();
            $storage->setOptions(['cookie_lifetime' => $container['session_lifetime']]);

            return new Session($storage);
        };
    }

    /**
     * @param string $configDir
     */
    private function processConfiguration($configDir)
    {
        $finder = new Finder();
        $finder->in(sprintf('%s/%s', $this->projectDir(), $configDir));
        $finder->ignoreDotFiles(true);
        $files = $finder->files()->name('*.yml');

        $configuration = new Configuration();
        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $configuration->addResource($file->getRealPath());
        }
        $configuration->process($this);
    }
}
