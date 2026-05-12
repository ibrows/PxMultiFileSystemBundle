<?php

use Gaufrette\Adapter\AwsS3;
use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Px\MultiFileSystemBundle\FilesystemMap;
use Px\MultiFileSystemBundle\Resolver\FilesystemResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('px_multi_file_system.filesystem', Filesystem::class)
        ->abstract()
        ->arg(0, null);

    $services->set('px_multi_file_system.adapter.local', Local::class)
        ->abstract()
        ->public(false)
        ->arg(0, null)
        ->arg(1, null);

    $services->set('px_multi_file_system.adapter.aws_s3', AwsS3::class)
        ->abstract()
        ->public(false);

    $services->set('px_multi_file_system.filesystem_map', FilesystemMap::class)
        ->arg(0, null);

    $services->set('px_multi_file_system.aws_s3.client', \Aws\S3\S3Client::class)
        ->abstract()
        ->factory([\Aws\S3\S3Client::class, 'factory']);

    $services->set('px_multi_file_system.filesystem_resolver', FilesystemResolver::class)
        ->arg(0, service('px_multi_file_system.filesystem_map'))
        ->arg(1, '%px_multi_file_system.default_adapter%');
};
