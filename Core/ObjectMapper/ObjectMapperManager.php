<?php
namespace Bun\Core\ObjectMapper;


use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\Container\ContainerAwareInterface;
use Bun\Core\Container\ContainerInterface;
use Bun\Core\Model\ModelInterface;

/**
 * Class ObjectMapperManager
 *
 * @package Bun\Core\ObjectMapper
 */
class ObjectMapperManager implements ConfigAwareInterface, ContainerAwareInterface, ObjectMapperInterface
{
    /** @var ConfigInterface */
    protected $config;
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ModelInterface $model
     * @return ObjectMapperInterface
     */
    public function getObjectMapper(ModelInterface $model)
    {
        $mapperServiceName = $model->getObjectMapperName();

        return $this->container->get($mapperServiceName);
    }

    /**
     * @param $object
     * @return array|mixed
     */
    public function save($object)
    {
        if(is_array($object)) {
            if(isset($object[0])) {
                $mapObject = $object[0];
            }
            else {
                return array();
            }
        }
        else {
            $mapObject = $object;
        }

        return $this->getObjectMapper($mapObject)->save($object);
    }

    /**
     * @param $className
     * @param $data
     * @param array $excludeRelation
     * @return ModelInterface
     */
    public function map($className, $data, $excludeRelation = array())
    {
        $mapObject = new $className;

        return $this->getObjectMapper($mapObject)->map($className, $data, $excludeRelation);
    }

    /**
     * @param $className
     * @param $data
     * @param array $aggregateRelations
     * @param array $excludeRelation
     * @return \Bun\Core\Model\ModelInterface[]
     */
    public function mapArray($className, $data, $aggregateRelations = array(), $excludeRelation = array())
    {
        $mapObject = new $className;

        return $this->getObjectMapper($mapObject)->mapArray($className, $data, $aggregateRelations, $excludeRelation);
    }

    /**
     * @param $className
     * @param $where
     * @param $orderBy
     * @param array $excludeRelations
     * @return \Bun\Core\Model\ModelInterface[]
     */
    public function mapRelationObjectsArray($className, $where, $orderBy, $excludeRelations = array())
    {
        $mapObject = new $className;

        return $this->getObjectMapper($mapObject)->mapRelationObjectsArray($className, $where, $orderBy, $excludeRelations);
    }

    /**
     * @param $className
     * @param $where
     * @param array $excludeRelation
     * @return ModelInterface
     */
    public function mapRelationObject($className, $where, $excludeRelation = array())
    {
        $mapObject = new $className;

        return $this->getObjectMapper($mapObject)->mapRelationObject($className, $where, $excludeRelation);
    }

    /**
     * @param $className
     * @return \Bun\Core\Storage\StorageInterface
     */
    public function getStorage($className)
    {
        $mapObject = new $className;

        return $this->getObjectMapper($mapObject)->getStorage($className);
    }

    /**
     * @param ModelInterface $object
     * @return bool|int
     */
    public function remove($object)
    {
        return $this->getObjectMapper($object)->remove($object);
    }

    /**
     * @param ModelInterface $object
     * @return ModelInterface|null
     */
    public function getMappedObjectCopy(ModelInterface $object)
    {
        return $this->getObjectMapper($object)->getMappedObjectCopy($object);
    }
}