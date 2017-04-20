<?php

namespace Ihsan\Client\Platform\Configuration;

use Pimple\Container;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@bisnis.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var string
     */
    private $configDir;

    /**
     * @param string $configDir
     */
    public function __construct($configDir = null)
    {
        $this->configDir = $configDir;
    }

    /**
     * @param Container $contianer
     */
    public function process(Container $contianer)
    {
        $configs = [];
        /** @var CacheItemPoolInterface $cache */
        $cache = $contianer['internal.cache_handler'];
        $item = str_replace('\\', '_', __CLASS__);
        if ($cache->hasItem($item)) {
            $this->merge($contianer, $cache->getItem($item)->get());

            return;
        }

        foreach ($this->configs as $config) {
            $configs = array_merge($configs, Yaml::parse(file_get_contents($config)));
        }

        $processor = new Processor();
        $configs = $processor->processConfiguration($this, $configs);
        $cache->save($cache->getItem($item)->set($configs));

        $this->merge($contianer, $cache->getItem($item)->get());
    }

    /**
     * @param string $resource
     */
    public function addResource($resource)
    {
        if ($this->configDir) {
            $this->configs[] = sprintf('%s/%s', $this->configDir, $resource);
        } else {
            $this->configs[] = $resource;
        }
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('base_url')->defaultValue(null)->end()
                ->scalarNode('http_client')->defaultValue(null)->end()
                ->arrayNode('middlewares')
                    ->defaultValue([])
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('parameters')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                                ->integerNode('priority')->defaultValue(0)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('event_listeners')
                    ->defaultValue([])
                    ->prototype('array')
                        ->children()
                            ->scalarNode('event')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('class')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('method')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->integerNode('priority')->defaultValue(0)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('routes')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('path')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('controller')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode('methods')
                                ->prototype('scalar')
                                    ->beforeNormalization()
                                        ->ifString()
                                        ->then(function ($v) { return strtoupper($v); })
                                    ->end()
                                    ->validate()
                                        ->ifNotInArray(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD'])
                                        ->thenInvalid('Invalid HTTP Verb')
                                    ->end()
                                    ->defaultValue(['GET'])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('template')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->children()
                        ->scalarNode('engine')->defaultValue(null)->end()
                        ->scalarNode('path')->defaultValue(null)->end()
                        ->scalarNode('cache_dir')->defaultValue(null)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @param Container $container
     * @param array     $configs
     */
    private function merge(Container $container, array $configs)
    {
        foreach ($configs as $key => $value) {
            $container[$key] = $value;
        }
    }
}
