<?php
namespace Bun\Core\Repository;

use Bun\Core\Config\ConfigInterface;
use Bun\Core\ObjectMapper\ObjectManagerAwareInterface;
use Bun\Core\Model\ModelInterface;

/**
 * Interface RepositoryInterface
 *
 * @package Bun\Core\Repository
 */
interface RepositoryInterface extends ObjectManagerAwareInterface
{
    /**
     * @param $id
     * @return ModelInterface
     */
    public function find($id);

    /**
     * @param $where
     * @param array $orderBy
     * @param array $limit
     * @return ModelInterface[]
     */
    public function findBy($where, $orderBy = array(), $limit = array());

    /**
     * @param $where
     * @param array $limit
     * @return int
     */
    public function count($where, $limit = array());

    /**
     * @param $className
     * @return $this
     */
    public function setModelClassName($className);

    /**
     * @param ConfigInterface $config
     * @return void
     */
    public function setConfig(ConfigInterface $config);

    /**
     * @param $clause
     * @param null|ModelInterface $object
     * @return array
     */
    public function mapClauseFields($clause, $object = null);
}