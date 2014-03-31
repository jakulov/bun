<?php
namespace Bun\Core\ObjectMapper;

use Bun\Core\Storage\StorageInterface;
use Bun\Core\Model\ModelInterface;

/**
 * Interface ObjectMapperInterface
 *
 * @package Bun\Core\ObjectMapper
 */
interface ObjectMapperInterface
{
    const SCHEMA_PART_FIELDS = 'fields';
    const RELATION_ONE_TO_MANY = 'oneToMany';
    const RELATION_MANY_TO_ONE = 'manyToOne';
    const RELATION_ONE_TO_ONE = 'oneToOne';
    const RELATION_MANY_TO_MANY = 'manyToMany';

    /**
     * @param $object
     * @return mixed
     */
    public function save($object);

    /**
     * @param $className
     * @param $data
     * @param array $excludeRelation
     * @return ModelInterface
     */
    public function map($className, $data, $excludeRelation = array());

    /**
     * @param $className
     * @param $data
     * @param array $aggregateRelations
     * @param array $excludeRelation
     * @return ModelInterface[]
     */
    public function mapArray($className, $data, $aggregateRelations = array(), $excludeRelation = array());

    /**
     * @param $className
     * @return StorageInterface
     */
    public function getStorage($className);

    /**
     * @param $className
     * @param $where
     * @param array $excludeRelation
     * @return ModelInterface
     */
    public function mapRelationObject($className, $where, $excludeRelation = array());

    /**
     * @param $className
     * @param $where
     * @param $orderBy
     * @param array $excludeRelations
     * @return ModelInterface[]
     */
    public function mapRelationObjectsArray($className, $where, $orderBy, $excludeRelations = array());

    /**
     * @param ModelInterface $object
     * @return bool|int
     */
    public function remove($object);

    /**
     * @param ModelInterface $object
     * @return null|ModelInterface
     */
    public function getMappedObjectCopy(ModelInterface $object);
}