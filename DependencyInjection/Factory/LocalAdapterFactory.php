<?php

namespace Px\MultiFileSystemBundle\DependencyInjection\Factory;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Local adapter factory
 */
class LocalAdapterFactory implements AdapterFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $container
            ->setDefinition($id, new DefinitionDecorator('px_multi_file_system.adapter.local'))
            ->replaceArgument(0, $config['directory'])
            ->replaceArgument(1, $config['create'])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'local';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->booleanNode('active')
                    ->defaultFalse()
                    ->beforeNormalization()
                        ->ifString()
                            ->then(function($v) { return in_array($v, array('1', 'true', 'on')); })
                    ->end()
                ->end()
                ->scalarNode('directory')->defaultValue('')->end()
                ->booleanNode('create')
                    ->defaultFalse()
                    ->beforeNormalization()
                        ->ifString()
                            ->then(function($v) { return in_array($v, array('1', 'true', 'on')); })
                    ->end()
                ->end()
            ->end()
        ;
    }
}
