<?php
namespace Bun\Cache;

/**
 * Class MemcacheDB
 *
 * @package Bun\Cache
 */
class MemcacheDB
{
    /** @var \Memcache */
    protected $con;

    protected $connected = false;

    /**
     * @param $host
     * @param $port
     * @throws MemcacheException
     */
    public function connect($host, $port)
    {
        $this->con = new \Memcache();
        $connected = $this->con->pconnect($host, $port);
        if ($connected === false) {
            throw new MemcacheException('Unable to connect to memcache instance host:'. $host .':'. $port);
        }

        $this->connected = true;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @return \Memcache
     */
    public function getConnection()
    {
        return $this->con;
    }

    /**
     * @param $key
     * @param $val
     * @param bool $compress
     * @param int $ttl
     * @return bool
     */
    public function set($key, $val, $compress = false, $ttl = 0)
    {
        return $this->con->set($key, $val, $compress, $ttl);
    }

    /**
     * @param $key
     * @return array|string
     */
    public function get($key)
    {
        return $this->con->get($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->con->delete($key);
    }

    /**
     * flushed storage
     */
    public function flush()
    {
        $this->con->flush();
    }
}