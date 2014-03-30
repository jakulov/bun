<?php
namespace Bun\Core\Storage;

use Bun\Core\Cache\CacheDriverInterface;

/**
 * Class StorageInterface
 *
 * @package Bun\Core\Storage
 */
interface StorageInterface
{
    /**
     * @param $table
     * @return $this
     */
    public function table($table);
    /**
     * @param array $where
     * @param array $orderBy
     * @param array $limit
     * @param bool $cache
     * @return mixed
     */
    public function findBy($where, $orderBy = null, $limit = array(), $cache = false);

    /**
     * @param $id
     * @return mixed|null
     */
    public function find($id);

    /**
     * @param $data
     * @param $where
     * @return bool|int
     */
    public function update($data, $where);

    /**
     * @param $data
     * @return bool|int
     * @throws StorageException
     */
    public function insert($data);

    /**
     * @param $where
     * @return bool|int
     */
    public function delete($where);

    /**
     * @return CacheDriverInterface
     */
    public function getCacheDriver();
}