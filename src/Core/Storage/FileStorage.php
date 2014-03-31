<?php
namespace Bun\Core\Storage;

use Bun\Core\Cache\FileCacheDriver;
use Bun\Core\Cache\FileCacheDriverAwareInterface;
use Bun\Core\File\File;
use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;

/**
 * Class FileStorage
 *
 * @package Bun\Core\Storage
 */
class FileStorage implements StorageInterface, ConfigAwareInterface, FileCacheDriverAwareInterface
{
    /** @var QueryParser */
    protected $queryParser;
    /** @var string */
    protected $storageDir = 'file_storage';
    /** @var FileCacheDriver */
    protected $fileCache;
    /** @var int */
    protected $storageCacheTtl = 36000;
    /** @var string */
    protected $storageFileExtension = 'store';
    /** @var resource */
    protected $readStorageContext;
    /** @var File[] */
    protected $cachedFiles = array();
    /** @var string */
    protected $table;

    /**
     *
     */
    public function __construct()
    {
        $this->queryParser = new QueryParser();
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $storageConfig = $config->get('core.file_storage');
        $configParams = array(
            'storageDir'           => 'dir',
            'storageCacheTtl'      => 'cache_ttl',
            'storageFileExtension' => 'file_extension',
        );
        $this->applyConfig($configParams, $storageConfig);
        $this->initStorage();
    }

    /**
     * @param array $params
     * @param array $config
     */
    protected function applyConfig($params = array(), $config = array())
    {
        foreach ($params as $field => $param) {
            $this->$field = (isset($config[$param])) ?
                $config[$param] :
                $this->$field;
        }
    }

    /**
     * @param FileCacheDriver $fileCache
     */
    public function setFileCacheDriver(FileCacheDriver $fileCache)
    {
        $this->fileCache = $fileCache;
    }

    /**
     *
     */
    protected function initStorage()
    {
        $dirName = VAR_DIR . DIRECTORY_SEPARATOR . $this->storageDir;
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }
    }

    /**
     * @param $id
     * @return array|mixed|null
     */
    public function find($id)
    {
        if (is_array($id)) {
            $result = array();
            foreach ($id as $inId) {
                $result[] = $this->find($inId);
            }

            return $result;
        }

        $file = $this->getFileById($id);

        return $file ?
            unserialize($file->getContent()) :
            null;
    }

    /**
     * @param $id
     * @param bool $create
     * @return File
     */
    protected function getFileById($id, $create = false)
    {
        if (isset($this->cachedFiles[$id]) && !$create) {
            return $this->cachedFiles[$id];
        }

        $storageQueryDir = VAR_DIR . DIRECTORY_SEPARATOR . $this->storageDir . DIRECTORY_SEPARATOR . $this->table;
        if (!is_dir($storageQueryDir)) {
            mkdir($storageQueryDir, 0777, true);
        }

        $filename = $storageQueryDir . DIRECTORY_SEPARATOR . $id . '.' . $this->storageFileExtension;
        $this->cachedFiles[$id] = new File($filename, $create);

        return $this->cachedFiles[$id];
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @param array $limit
     * @param bool $cache
     * @return array
     */
    public function findBy($where, $orderBy = array(), $limit = array(), $cache = false)
    {
        $cacheValue = false;
        if ($cache) {
            $hash = $this->getQueryHash($where, $orderBy, $limit);
            $cacheValue = $this->fileCache->get($hash, $this->storageCacheTtl);
        }

        if (!$cacheValue) {
            $data = $this->getStoredDataBy($where, $orderBy, $limit);
            if ($cache) {
                $this->fileCache->set($hash, serialize($data), $this->storageCacheTtl, $this->table);
            }
        }
        else {
            $data = unserialize($cacheValue);
        }

        return $data;
    }

    /**
     * @param $where
     * @param array $orderBy
     * @param array $limit
     * @return array
     */
    protected function getStoredDataBy($where, $orderBy = array(), $limit = array())
    {
        $data = array();
        $whereClauses = $this->queryParser->parseWhere($where);
        $byIdClause = $this->getByIdClauseFromWhere($whereClauses);

        if ($byIdClause && $byIdClause->isByIdClause('equals')) {
            $data[$byIdClause->getOperand2()] = $this->find($byIdClause->getOperand2());

            return $data;
        }

        $this->clearReadStorageContext();
        while ($object = $this->readStorage()) {
            if ($byIdClause === false) {
                $params = unserialize($object->getContent());
                if ($this->queryParser->applyClauses($whereClauses, $params)) {
                    $data[$object->getName(true)] = $params;
                }
            }
            else {
                if ($byIdClause->apply((int)$object->getName(true))) {
                    $data[$object->getName(true)] = unserialize($object->getContent());
                }
            }
        }

        $this->clearReadStorageContext();

        if (is_array($orderBy)) {
            foreach ($orderBy as $field => $dimension) {
                usort($data, function ($a, $b) use ($field, $dimension) {
                    if ($dimension === 1) {
                        return ($a[$field] < $b[$field]) ? -1 : 1;
                    }
                    else {
                        return $b[$field] < $a[$field] ? -1 : 1;
                    }
                });
            }
        }

        if ($limit) {
            $data = array_slice($data, $limit[0], $limit[1]);
        }

        return $data;
    }

    /**
     * @param $whereClauses
     * @return bool|QueryClause
     */
    protected function getByIdClauseFromWhere($whereClauses)
    {
        if (
            count($whereClauses) === 1 &&
            array_key_exists('$and', $whereClauses) &&
            count($whereClauses['$and']) === 1
        ) {
            /** @var QueryClause $clause */
            $clause = $whereClauses['$and'][0];
            if ($clause->isByIdClause('equals')) {
                return $clause;
            }
            elseif ($clause->isByIdClause()) {
                return $clause;
            }
        }

        return false;
    }

    /**
     * @param $where
     * @return File[]
     */
    protected function getStoredFilesBy($where)
    {
        $data = array();
        $whereClauses = $this->queryParser->parseWhere($where);
        $byIdClause = $this->getByIdClauseFromWhere($whereClauses);

        if ($byIdClause && $byIdClause->isByIdClause('equals')) {
            $storageQueryDir = VAR_DIR . DIRECTORY_SEPARATOR . $this->storageDir . DIRECTORY_SEPARATOR . $this->table;

            return array(
                (string)$byIdClause->getOperand2() =>
                    new File(
                        $storageQueryDir . DIRECTORY_SEPARATOR .
                        $byIdClause->getOperand2() . ' . ' . $this->storageFileExtension,
                        true
                    )
            );
        }

        while ($object = $this->readStorage()) {
            if ($byIdClause === false) {
                $params = unserialize($object->getContent());
                if ($this->queryParser->applyClauses($whereClauses, $params)) {
                    $data[$object->getName(true)] = $object;
                }
            }
            else {
                if ($byIdClause->apply((int)$object->getName(true))) {
                    $data[$object->getName(true)] = $object;
                }
            }
        }
        $this->clearReadStorageContext();

        return $data;
    }

    /**
     *
     */
    protected function clearReadStorageContext()
    {
        $this->readStorageContext = null;
    }

    /**
     * @return bool|File
     */
    protected function readStorage()
    {
        $dirName = VAR_DIR . DIRECTORY_SEPARATOR . $this->storageDir . DIRECTORY_SEPARATOR . $this->table;

        if ($this->readStorageContext === null) {
            $this->readStorageContext = opendir($dirName);
        }

        $fileHandler = readdir($this->readStorageContext);
        if ($fileHandler !== false && !is_file($dirName . DIRECTORY_SEPARATOR . $fileHandler)) {
            return $this->readStorage();
        }

        return ($fileHandler !== false) ?
            new File($dirName . DIRECTORY_SEPARATOR . $fileHandler) :
            false;
    }

    /**
     * @return bool
     */
    public function clearStorageCache()
    {
        return $this->fileCache->clear($this->table);
    }

    /**
     * @param $where
     * @param $orderBy
     * @param $limit
     * @return string
     */
    protected function getQueryHash($where, $orderBy, $limit)
    {
        $hashSting = $this->getArrayHash('', $where);
        $hashSting .= $this->getArrayHash('_order_by', $orderBy);
        $hashSting .= $this->getArrayHash('_limit_', $limit);

        return md5($hashSting);
    }

    /**
     * @param $hash
     * @param $array
     * @return string
     */
    protected function getArrayHash($hash, $array)
    {
        if (count($array) > 0) {
            foreach ($array as $key => $val) {
                $hash .= $key . '=>';
                if (is_array($val)) {
                    return $this->getArrayHash($hash, $val);
                }
                $hash .= $val;
            }
        }

        return $hash;
    }

    /**
     * @param $data
     * @param $where
     * @return bool|int
     * @throws StorageException
     */
    public function update($data, $where)
    {
        if ($this->isTableContext()) {
            $oldData = $this->findBy($where);
            $updated = 0;
            foreach ($oldData as $id => $oldObj) {
                $newObj = $this->updateObjData($oldObj, $data);
                $file = $this->getFileById($newObj['id']);
                if ($file->setContent(serialize($newObj), true)) {
                    $updated++;
                }
            }

            if ($updated) {
                $this->clearStorageCache();
            }

            return $updated ? $updated : false;
        }

        throw new StorageException('Update operation applicable only in table context');
    }

    /**
     * @param $oldObj
     * @param $data
     *
     * @return mixed
     */
    protected function updateObjData($oldObj, $data)
    {
        foreach ($data as $field => $value) {
            $oldObj[$field] = $value;
        }

        return $oldObj;
    }

    /**
     * @param $data
     * @return bool|int
     * @throws StorageException
     */
    public function insert($data)
    {
        if ($this->isTableContext()) {
            $id = isset($data['id']) && !empty($data['id']) ?
                (int)$data['id'] :
                $this->generateId();

            $data['id'] = $id;

            $file = $this->getFileById($id, false);
            if ($file->getName() !== null) {
                throw new StorageException(
                    'Unable to insert new record, id file already exists: ' .
                    $this->table . '/' . $id . '.' . $this->storageFileExtension
                );
            }
            $file = $this->getFileById($id, true);
            $written = (bool)$file->setContent(serialize($data), true);

            if ($written) {
                $this->clearStorageCache();
            }

            return ($written) ?
                $id :
                $written;
        }

        throw new StorageException('Insert operation only applicable in table context');
    }

    /**
     * @param $where
     * @return bool|int
     * @throws StorageException
     */
    public function delete($where)
    {
        if ($this->isTableContext()) {
            $deleted = 0;
            $files = $this->getStoredFilesBy($where);
            foreach ($files as $file) {
                if ($file->remove()) {
                    $deleted++;
                }
            }

            if ($deleted) {
                $this->clearStorageCache();
            }

            return $deleted ? $deleted : false;
        }

        throw new StorageException('Delete operation applicable only in table context');
    }

    /**
     * @return int
     */
    protected function generateId()
    {
        return mt_rand(1000000, 9999999);
    }

    /**
     * @return bool
     */
    public function clear()
    {
        if ($this->table === null) {
            $tables = array();
            $storageDir = VAR_DIR . DIRECTORY_SEPARATOR . $this->storageDir;
            $storageHandler = opendir($storageDir);
            while ($storageTable = readdir($storageHandler)) {
                if (
                    $storageTable !== '.' && $storageTable !== '..' &&
                    is_dir($storageDir . DIRECTORY_SEPARATOR . $storageTable)
                ) {
                    $tables[] = $storageTable;
                }
            }
        }
        else {
            $tables = array(
                $this->table
            );
        }
        foreach ($tables as $table) {
            $this
                ->table($table)
                ->clearReadStorageContext();
            while ($file = $this->readStorage()) {
                $file->remove();
            }
            $this->clearStorageCache();
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isTableContext()
    {
        return !empty($this->table);
    }

    /**
     * @return \Bun\Core\Cache\CacheDriverInterface|FileCacheDriver
     */
    public function getCacheDriver()
    {
        return $this->fileCache;
    }
}