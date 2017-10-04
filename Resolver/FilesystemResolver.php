<?php

namespace Px\MultiFileSystemBundle\Resolver;

use Px\MultiFileSystemBundle\FilesystemMap;

class FilesystemResolver
{
    /**
     * @var FilesystemMap
     */
    protected $filesystemMap;
    /**
     * @var string
     */
    protected $defaultAdapter;

    /**
     * FileSystemResolver constructor.
     * @param FilesystemMap $filesystemMap
     * @param string        $defaultAdapter
     */
    public function __construct(FilesystemMap $filesystemMap, $defaultAdapter)
    {
        $this->filesystemMap = $filesystemMap;
        $this->defaultAdapter = $defaultAdapter;
    }

    /**
     * @param string $defaultAdapter
     * @return FileSystemResolver
     */
    public function setDefaultAdapter($defaultAdapter)
    {
        $this->defaultAdapter = $defaultAdapter;

        return $this;
    }

    /**
     * @param string      $context
     * @param null|string $adapter
     * @return \Gaufrette\Filesystem
     */
    public function get($context, $adapter = null)
    {
        if (null === $adapter) {
            $adapter = $this->defaultAdapter;
        }
        $filesystem = sprintf('%s_%s', $adapter, $context);

        return $this->filesystemMap->get($filesystem);
    }
}
