<?php
namespace Bun\Cache;

use Bun\Core\Cache\CacheDriverInterface;
use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Session\SessionStorageInterface;

/**
 * Class MemcacheCacheDriver
 *
 * @package Bun\Cache
 */
class MemcacheCacheDriver implements CacheDriverInterface, ConfigAwareInterface, SessionStorageInterface
{
    /** @var ConfigInterface */
    protected $config;
    /** @var MemcacheDB */
    protected $memcache;

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * Init connection to Memcache
     * @throws MemcacheException
     */
    protected function init()
    {
        $this->memcache = new MemcacheDB();
        $driverConfig = $this->config->get('cache.memcache');
        $this->memcache->connect(
            $driverConfig['host'],
            $driverConfig['port']
        );
    }

    /**
     * @return string
     */
    public function generateId()
    {
        return md5(microtime(false));
    }

    /**
     * @param $key
     * @param $val
     * @param null $ttl
     * @param null $namespace
     * @param bool $compress
     * @return bool
     */
    public function set($key, $val, $ttl = null, $namespace = null, $compress = false)
    {
        if($namespace !== null) {
            $nsKey = $this->getNameSpaceKey($namespace);
            $nsValue = $this->get($nsKey);
            $nsValue = !$nsValue ?
                array() :
                unserialize($nsValue);
            if(!in_array($key, $nsValue)) {
                $nsValue[] = $key;
            }
            $this->set($nsKey, serialize($nsValue));
        }

        return $this->memcache->set($key, $val, $compress, (int)$ttl);
    }

    /**
     * @param $key
     * @return array|string
     */
    public function get($key)
    {
        return $this->memcache->get($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->memcache->delete($key);
    }

    /**
     * @param null $namespace
     * @return bool
     */
    public function clear($namespace = null) {
        if($namespace !== null) {
            $nsKey = $this->getNameSpaceKey($namespace);
            $nsValue = unserialize($this->get($nsKey));
            if(is_array($nsValue)) {
                foreach($nsValue as $key) {
                    $this->memcache->delete($key);
                }
            }
            $this->set($nsKey, null);

            return true;
        }

        $this->memcache->flush();

        return true;
    }

    /**
     * @param $namespace
     * @return string
     */
    protected function getNameSpaceKey($namespace)
    {
        return '_cacheNS.' . $namespace;
    }
}