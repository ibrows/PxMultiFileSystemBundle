<?php

namespace Px\MultiFileSystemBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Main configuration for the Gaufrette DIC extension
 */
class MainConfiguration implements ConfigurationInterface
{
    private $factories;

    /**
     * Constructor
     *
     * @param  array $factories
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * Generates the configuration tree builder
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder
            ->root('px_multi_file_system')
            ->children()
                ->scalarNode('default_adapter')->defaultValue('')->end()
            ->end();

        $this->addAdaptersSection($rootNode, $this->factories);
        $this->addFilesystemsSection($rootNode);

        $rootNode
            // add a faux-entry for factories, so that no validation error is thrown
            ->fixXmlConfig('factory', 'factories')
            ->children()
                ->arrayNode('factories')->ignoreExtraKeys()->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function addAdaptersSection(ArrayNodeDefinition $node, array $factories)
    {
        $adapterNodeBuilder = $node
            ->fixXmlConfig('adapter')
            ->children()
                ->arrayNode('adapters')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->performNoDeepMerging()
                    ->children()
        ;

        foreach ($factories as $name => $factory) {
            $factoryNode = $adapterNodeBuilder->arrayNode($name)->canBeUnset();
            $factory->addConfiguration($factoryNode);
        }
    }

    private function addFilesystemsSection(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('context')
            ->children()
                ->arrayNode('contexts')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('directory')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
