<?php
namespace Bun\Core\Cache;

use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\File\File;

/**
 * Class FileCacheDriver
 *
 * @package Bun\Core\Cache
 */
class FileCacheDriver implements CacheDriverInterface, ConfigAwareInterface
{
    protected $ttl = 3600;
    protected $storageDir = 'file_cache';
    protected $config;

    /**
     * @param ConfigInterface $config
     * @throws CacheException
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        $cacheTtl = $this->config->get('cache.file.ttl');
        $cacheDir = $this->config->get('cache.file.dir');
        $this->ttl = $cacheTtl ? $cacheTtl : $this->ttl;
        $this->storageDir = $cacheDir ? $cacheDir : $this->storageDir;
        $cacheDir = $this->getCacheDir();
        if(!is_dir($cacheDir)) {
            $make = mkdir($cacheDir, 0777, true);
            if(!$make) {
                throw new CacheException('Unable to create cache storage directory: '. $cacheDir);
            }
        }
    }

    /**
     * @param $key
     * @param null $ttl
     * @return null|string
     */
    public function get($key, $ttl = null)
    {
        $file = $this->getCacheFile($key);
        $ttlTime = time() - $ttl;
        if($ttl !== null && $file instanceof File && $file->modifiedLater($ttlTime)) {
            $file->remove();
            $file = null;
        }
        if($file !== null) {
            return $file->getContent();
        }

        return $file;
    }

    /**
     * @param $key
     * @param $value
     * @param null $ttl
     * @param null $namespace
     * @return bool|int|null
     */
    public function set($key, $value, $ttl = null, $namespace = null)
    {
        $file = $this->getCacheFile($key, true);

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

        return $file->setContent($value, true);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $file = $this->getCacheFile($key);
        if($file instanceof File) {
            return $file->remove();
        }

        return true;
    }

    /**
     * @param null $namespace
     * @return bool
     */
    public function clear($namespace = null)
    {
        if($namespace === null) {
            $cacheDirHandler = opendir($this->getCacheDir());
            while($f = readdir($cacheDirHandler)) {
                if(
                    file_exists($this->getCacheDir() . DIRECTORY_SEPARATOR . $f) &&
                    is_file($this->getCacheDir() . DIRECTORY_SEPARATOR . $f)
                ) {
                    unlink($this->getCacheDir() . DIRECTORY_SEPARATOR . $f);
                }
            }

            return true;
        }

        $nsKey = $this->getNameSpaceKey($namespace);
        $nsValue = unserialize($this->get($nsKey));
        if(is_array($nsValue)) {
            foreach($nsValue as $key) {
                $file = $this->getCacheFile($key);
                if($file instanceof File) {
                    $file->remove();
                }
            }
        }
        $this->set($nsKey, null);

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

    /**
     * @return string
     */
    protected function getCacheDir()
    {
        return VAR_DIR . DIRECTORY_SEPARATOR . $this->storageDir;
    }

    /**
     * @param $key
     * @param bool $create
     * @return File|null
     */
    protected function getCacheFile($key, $create = false)
    {
        $fileName = $this->getCacheDir() . DIRECTORY_SEPARATOR . $key .'.cache';

        if(!$create && File::exists($fileName)) {
            return new File($fileName, $create);
        }
        elseif($create) {
            return new File($fileName, $create);
        }

        return null;
    }
}