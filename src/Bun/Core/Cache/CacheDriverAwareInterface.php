<?php
namespace Bun\Core\Cache;

/**
 * Interface CacheDriverAwareInterface
 *
 * @package Bun\Core\Cache
 */
interface CacheDriverAwareInterface
{
    /**
     * @param CacheDriverInterface $cacheDriver
     * @return void
     */
    public function setCacheDriver(CacheDriverInterface $cacheDriver);
}