<?php

namespace Px\MultiFileSystemBundle\DependencyInjection\Factory;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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
                ->booleanNode('active')
                    ->defaultFalse()
                    ->beforeNormalization()
                        ->ifString()
                            ->then(function($v) { return in_array($v, array('1', 'true', 'on')); })
                    ->end()
                ->end()
                ->booleanNode('detect_content_type')
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
                ->scalarNode('acl')->defaultValue('private')->end()
                ->arrayNode('s3_config')->isRequired()
                    ->children()
                        ->scalarNode('bucket_name')->end()
                        //->scalarNode('bucket_name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('version')->end()
                        //->scalarNode('version')->defaultValue('latest')->end()
                        ->scalarNode('region')->end()
                        //->scalarNode('region')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('credentials')
                            ->children()
                                ->scalarNode('key')->end()
                                //->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('secret')->end()
                                //->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($config) {
                    return $this->validateConfig($config);
                })
                ->then(function($config) {
                    return true;
                })
                ->end()
            ->end()
        ;
    }

    private function validateConfig($config)
    {
        if (true === $config['active']) {
            if (null === $config['s3_config']['bucket_name']) {
                $this->cannotBeNullConfiguration('px_multi_file_system.adapters.aws.aws_s3.s3_config.bucket_name');
            }
            if (null === $config['s3_config']['version']) {
                $this->cannotBeNullConfiguration('px_multi_file_system.adapters.aws.aws_s3.s3_config.version');
            }
            if (null === $config['s3_config']['region']) {
                $this->cannotBeNullConfiguration('px_multi_file_system.adapters.aws.aws_s3.s3_config.region');
            }
            if (!isset($config['s3_config']['credentials'])) {
                $this->cannotBeNullConfiguration('px_multi_file_system.adapters.aws.aws_s3.s3_config.credentials');
            }
            if (null === $config['s3_config']['credentials']['key']) {
                $this->cannotBeNullConfiguration('px_multi_file_system.adapters.aws.aws_s3.s3_config.credentials.key');
            }
            if (null === $config['s3_config']['credentials']['secret']) {
                $this->cannotBeNullConfiguration('px_multi_file_system.adapters.aws.aws_s3.s3_config.credentials.secret');
            }
        }
    }

    private function cannotBeNullConfiguration($name)
    {
        throw new InvalidConfigurationException("The path \"$name\" cannot contain an empty value, but got null.");
    }
}
