<?php
namespace Bun\Core\Cache;

/**
 * Interface FileCacheDriverAwareInterface
 *
 * @package Bun\Core\Cache
 */
interface FileCacheDriverAwareInterface
{
    /**
     * @param FileCacheDriver $cacheDriver
     */
    public function setFileCacheDriver(FileCacheDriver $cacheDriver);
}