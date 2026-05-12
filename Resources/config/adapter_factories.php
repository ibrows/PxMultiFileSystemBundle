<?php

use Px\MultiFileSystemBundle\DependencyInjection\Factory\AwsS3AdapterFactory;
use Px\MultiFileSystemBundle\DependencyInjection\Factory\LocalAdapterFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('px_multi_file_system.adapter.factory.local', LocalAdapterFactory::class)
        ->tag('px_multi_file_system.adapter.factory');

    $services->set('px_multi_file_system.adapter.factory.aws_s3', AwsS3AdapterFactory::class)
        ->tag('px_multi_file_system.adapter.factory');
};
