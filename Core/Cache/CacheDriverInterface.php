<?php
namespace Bun\Core\Cache;

/**
 * Interface CacheDriverInterface
 *
 * @package Bun\Core\Cache
 */
interface CacheDriverInterface
{
    public function set($key, $value, $ttl = null, $namespace = null);

    public function get($key);

    public function delete($key);

    public function clear($namespace = null);
}