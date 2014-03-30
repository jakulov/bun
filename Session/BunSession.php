<?php
namespace Bun\Session;

use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;

/**
 * Class BunSession
 *
 * @package Bun\Session
 */
class BunSession implements ConfigAwareInterface, SessionStorageAwareInterface, SessionInterface
{
    /** @var ConfigInterface */
    protected $config;
    /** @var int */
    protected $ttl = 900;
    /** @var SessionStorageInterface */
    protected $storage;
    /** @var string */
    protected $cookieName = 'SID';
    /** @var null|string */
    protected $cookieDomain = null;
    /** @var bool */
    protected $isStarted = false;
    /** @var array */
    protected $data = array();
    /** @var null|string */
    protected $id = null;

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * @param SessionStorageInterface $sessionStorage
     */
    public function setSessionStorage(SessionStorageInterface $sessionStorage)
    {
        $this->storage = $sessionStorage;
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $value
     */
    public function setFlash($value)
    {
        if(!$this->isStarted) {
            $this->start();
        }

        if(!isset($this->data['flashes'])) {
            $this->data['flashes'] = array();
        }

        $this->data['flashes'][] = $value;
    }

    /**
     * @return array
     */
    public function getFlash()
    {
        $flashes = $this->get('flashes');
        $this->delete('flashes');

        return !is_array($flashes) ?
            array() :
            $flashes;
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        $val = $this->get($key);
        if($val !== null) {
            if(strpos($key, '.') !== false) {
                $this->recursiveDelete(explode('.', $key), $this->data);
            }
            else {
                unset($this->data[$key]);
            }
        }
    }

    /**
     * @param $keyParts
     * @param $data
     */
    protected function recursiveDelete($keyParts, $data)
    {
        $key = array_shift($keyParts);
        if (isset($data[$key])) {
            if(!$keyParts) {
                unset($data[$key]);
                return;
            }
            else {
                $this->recursiveDelete($keyParts, $data[$key]);
            }
        }

        return;
    }

    /**
     * Initialize session config
     */
    protected function init()
    {
        $sessionConfig = $this->config->get('session');
        $this->ttl = isset($sessionConfig['ttl']) ? $sessionConfig['ttl'] : $this->ttl;
        $this->cookieName = isset($sessionConfig['cookie_name']) ?
            $sessionConfig['cookie_name'] :
            $this->cookieName;
        $this->cookieDomain = isset($sessionConfig['cookie_domain']) ?
            $sessionConfig['cookie_domain'] :
            $this->cookieDomain;
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->isStarted;
    }

    /**
     * @return string
     */
    protected function generateId()
    {
        return $this->storage->generateId();
    }

    /**
     * Try to load session from cookie SID
     * @return bool
     */
    protected function loadFromGlobals()
    {
        if (isset($_COOKIE[$this->cookieName])) {
            $sid = $_COOKIE[$this->cookieName];
            $data = $this->storage->get($sid);
            if ($data) {
                $this->isStarted = true;
                $this->data = $data;
                $this->id = $sid;

                return $this->isStarted;
            }

            return $this->isStarted;
        }

        return false;
    }

    /**
     * Returns true on success
     *
     * @return bool
     */
    public function start()
    {
        if (!$this->isStarted) {
            $loaded = $this->loadFromGlobals();
            if (!$loaded) {
                $this->id = $this->generateId();
                $this->data = array();
                $this->isStarted = true;
            }
        }

        return $this->isStarted;
    }

    /**
     * Returns true on success
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->isStarted) {
            $this->storage->delete($this->id);
            $this->isStarted = false;
            $this->data = array();
            setcookie($this->cookieName, '', -1, '/', $this->cookieDomain, false, true);
            $this->id = null;
        }

        return !$this->isStarted;
    }

    /**
     * Setting session cookie
     */
    public function sendCookie()
    {
        if ($this->id !== null) {
            setcookie($this->cookieName, $this->id, time() + $this->ttl, '/', $this->cookieDomain, false, true);
        }
    }

    /**
     * Saves session data in storage
     */
    public function save()
    {
        if ($this->id !== null) {
            $this->storage->set($this->id, $this->data, $this->ttl);
            $this->sendCookie();
        }
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        $loaded = $this->loadFromGlobals();
        if ($loaded) {
            if (strpos($key, '.') !== false) {
                $keyParts = explode('.', $key);

                return $this->recursiveGet($keyParts, $this->data);
            }
            else {
                return isset($this->data[$key]) ?
                    $this->data[$key] :
                    null;
            }
        }

        return null;
    }

    /**
     * @param array $keyParts
     * @param array $data
     * @return mixed|null
     */
    protected function recursiveGet($keyParts = array(), $data = array())
    {
        $key = array_shift($keyParts);
        if (isset($data[$key])) {
            return (!$keyParts) ?
                $data[$key] :
                $this->recursiveGet($keyParts, $data[$key]);
        }

        return null;
    }

    /**
     * @param $key
     * @param $val
     */
    public function set($key, $val)
    {
        $this->start();
        if(strpos($key, '.') !== false) {
            $this->recursiveSet($key, $val, $this->data);
        }
        else {
            $this->data[$key] = $val;
        }
    }

    /**
     * @param $path
     * @param $value
     * @param $arr
     */
    protected function recursiveSet($path, $value, &$arr)
    {
        $keys = explode('.', $path);

        while ($key = array_shift($keys)) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }
}