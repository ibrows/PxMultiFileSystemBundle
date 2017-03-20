Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require px/multi-file-system-bundle "~1"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Px\MultiFileSystemBundle\PxMultiFileSystemBundle(),
        );

        // ...
    }

    // ...
}
```

Configuration
=============

The bundle allows you to declare your adapters as services.

The configuration of the bundle is divided into two parts: the `contexts` and the `adapters`.

## Configuring the Contexts

``` yaml
# app/config/config.yml
px_multi_file_system:
    contexts:
        user_profile:
            directory: '/path/to/directory'
```

## Configuring the Local Adapter

A simple local filesystem based adapter.

### Parameters

 * `directory` The directory of the filesystem *(required)*
 * `create` Whether to create the directory if it does not exist *(default true)*

### Example

``` yaml
# app/config/config.yml
px_multi_file_system:
    adapters:
        foo:
            local:
                directory:  /path/to/directory
                create:     true
```

## Configuring the Amazon S3 Adapter

### Parameters

* `create` Whether to create the bucket if it doesn't exist. *(default false)*
* `directory` A directory to operate in. *(default '')*
 * `s3_config` A list of additional options passed to the adapter.
   This directory will be created in the root of the bucket and all files will be read and written there.
   * `acl` Default ACL to apply to the objects
   * `bucket_name` The name of the S3 bucket to use. *(required)*
   * `version` The version of the S3 bucket to use. *(required)*
   * `credentials` The credentials of the S3 bucket to use. *(required)*

### Example

``` yaml
# app/config/config.yml
px_multi_file_system:
    adapters:
        profile_photos:
            aws_s3:
                directory: 'default'
                create: true
                s3_config:
                  bucket_name: %amazon_s3.bucket%
                  version: latest
                  region: %amazon_s3.region%
                  credentials:
                    key: %amazon_s3.key%
                    secret: %amazon_s3.secret%

```
