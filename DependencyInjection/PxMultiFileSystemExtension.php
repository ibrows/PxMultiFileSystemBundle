<?php

namespace Px\MultiFileSystemBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class PxMultiFileSystemExtension extends Extension
{
    private $factories = null;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config = $processor->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setParameter('px_multi_file_system.default_adapter', $config['default_adapter']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('filesystem.xml');

        $adapters = array();

        $map = array();
        foreach ($config['adapters'] as $adapterName => $adapter) {
            reset($adapter);
            $key = key($adapter);
            if (true !== $adapter[$key]['active']) {
                continue;
            }
            $baseDirectory = $adapter[$key]['directory'];
            foreach ($config['contexts'] as $filesystemName => $filesystem) {
                if (isset($filesystem['directory'])) {
                    $adapter[$key]['directory'] = $baseDirectory.$filesystem['directory'];
                }
                $name = sprintf('%s_%s', $adapterName, $filesystemName);
                $adapters[$name] = $this->createAdapter($name, $adapter, $container, $this->factories);
                $map[$name] = $this->createFilesystem($name, $filesystem, $container, $adapters[$name]);
            }
        }

        $container->getDefinition('px_multi_file_system.filesystem_map')
            ->replaceArgument(0, $map);
    }

    public function getConfiguration(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();

        // first assemble the adapter factories
        $factoryConfig = new FactoryConfiguration();
        $config        = $processor->processConfiguration($factoryConfig, $configs);
        $factories     = $this->createAdapterFactories($config, $container);

        // then normalize the configs
        return new MainConfiguration($factories);
    }

    private function createAdapter($name, array $config, ContainerBuilder $container, array $factories)
    {
        reset($config);
        $key = key($config);
        $adapter = $config[$key];
        if (array_key_exists($key, $factories)) {
            $id = sprintf('px_multi_file_system.%s_adapter', $name);
            $factories[$key]->create($container, $id, $adapter);

            return $id;
        }

        throw new \LogicException(sprintf('The adapter \'%s\' is not configured.', $name));
    }

    /**
     * @return Reference a reference to the created filesystem
     */
    private function createFilesystem($name, array $config, ContainerBuilder $container, $adapter)
    {
        $id = sprintf('px_multi_file_system.%s_filesystem', $name);

        $container
            ->setDefinition($id, new DefinitionDecorator('px_multi_file_system.filesystem'))
            ->replaceArgument(0, new Reference($adapter))
        ;

        $container->getDefinition($id)->setPublic(false);
        $container->setAlias(sprintf('%s', $name), $id);

        return new Reference($id);
    }

    /**
     * Creates the adapter factories
     *
     * @param  array            $config
     * @param  ContainerBuilder $container
     */
    private function createAdapterFactories($config, ContainerBuilder $container)
    {
        if (null !== $this->factories) {
            return $this->factories;
        }

        // load bundled adapter factories
        $tempContainer = new ContainerBuilder();
        $parameterBag  = $container->getParameterBag();
        $loader        = new XmlFileLoader($tempContainer, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('adapter_factories.xml');

        // load user-created adapter factories
        foreach ($config['factories'] as $factory) {
            $loader->load($parameterBag->resolveValue($factory));
        }

        $services  = $tempContainer->findTaggedServiceIds('px_multi_file_system.adapter.factory');
        $factories = array();
        foreach (array_keys($services) as $id) {
            $factory = $tempContainer->get($id);
            $factories[str_replace('-', '_', $factory->getKey())] = $factory;
        }

        return $this->factories = $factories;
    }
}
