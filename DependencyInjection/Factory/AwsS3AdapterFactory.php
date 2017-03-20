<?php

namespace Px\MultiFileSystemBundle\DependencyInjection\Factory;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class AwsS3AdapterFactory implements AdapterFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $s3ClientId = sprintf('%s.aws_s3.client', $id);
        $options = array(
            'directory' => $config['directory'],
            'create'    => $config['create'],
            'acl'       => $config['acl'],
        );
        $container
            ->setDefinition($id, new DefinitionDecorator('px_multi_file_system.adapter.aws_s3'))
            ->addArgument(new Reference($s3ClientId))
            ->addArgument($config['s3_config']['bucket_name'])
            ->addArgument($options)
            ->addArgument($config['detect_content_type']);
        $s3Config = [
            'version'     => $config['s3_config']['version'],
            'region'      => $config['s3_config']['region'],
            'credentials' => $config['s3_config']['credentials'],
        ];
        $container
            ->setDefinition($s3ClientId, new DefinitionDecorator('px_multi_file_system.aws_s3.client'))
            ->addArgument($s3Config)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'aws_s3';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(NodeDefinition $builder)
    {
        $builder
            ->children()
                ->booleanNode('detect_content_type')->defaultFalse()->end()
                ->scalarNode('directory')->defaultValue('')->end()
                ->booleanNode('create')->defaultFalse()->end()
                ->scalarNode('acl')->defaultValue('private')->end()
                ->arrayNode('s3_config')
                    ->children()
                        ->scalarNode('bucket_name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('version')->defaultValue('latest')->end()
                        ->scalarNode('region')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('credentials')
                            ->children()
                                ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->end()
        ;
    }
}
