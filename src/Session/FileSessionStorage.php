<?php
namespace Bun\Session;

use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;

/**
 * Class FileSessionStorage
 *
 * @package Bun\Session
 */
class FileSessionStorage implements SessionStorageInterface, ConfigAwareInterface
{
    /** @var ConfigInterface */
    protected $config;
    /** @var string */
    protected $storageDir = 'session_storage';

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * Initialize storage
     */
    protected function init()
    {
        $storageConfig = $this->config->get('session.file_storage');
        $this->storageDir = isset($storageConfig['dir']) ?
            VAR_DIR . DIRECTORY_SEPARATOR . $storageConfig['dir'] :
            VAR_DIR . DIRECTORY_SEPARATOR . $this->storageDir;

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }
    }

    /**
     * @param $key
     * @return string
     */
    protected function getStorageFileName($key)
    {
        return $this->storageDir . DIRECTORY_SEPARATOR . $key . '.session';
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        $fileName = $this->getStorageFileName($key);
        if(file_exists($fileName)) {
            $data = unserialize(file_get_contents($fileName));
            $ttl = isset($data['__storage_ttl']) ? (int)$data['_storage_ttl'] : null;
            if($ttl === null || $ttl < time()) {
                unset($data['__storage_ttl']);
                return $data;
            }
        }

        return null;
    }

    /**
     * @param $key
     * @param $val
     * @param null $ttl
     * @return bool
     */
    public function set($key, $val, $ttl = null)
    {
        $fileName = $this->getStorageFileName($key);

        if($ttl !== null) {
            $val['__storage_ttl'] = time() + (int)$ttl;
        }

        return (file_put_contents($fileName, serialize($val)) !== false);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $fileName = $this->getStorageFileName($key);
        if(file_exists($fileName)) {
            return unlink($fileName);
        }

        return true;
    }

    /**
     * @return string
     */
    public function generateId()
    {
        return md5(SECRET_SALT . mt_rand(100000, 999999) . microtime());
    }
}