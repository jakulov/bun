<?php
namespace Bun\Core\Repository;

use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\Model\ModelInterface;
use Bun\Core\ObjectMapper\ObjectMapperInterface;

/**
 * Class AbstractRepository
 *
 * @package Bun\Core\Repository
 */
abstract class AbstractRepository implements RepositoryInterface, ConfigAwareInterface
{
    /** @var ObjectMapperInterface */
    protected $objectMapper;
    /** @var string */
    protected $className;
    /** @var ModelInterface */
    protected $modelObject;
    /** @var ConfigInterface  */
    protected $config;

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param ObjectMapperInterface $objectMapper
     * @return mixed|void
     */
    public function setObjectManager(ObjectMapperInterface $objectMapper)
    {
        $this->objectMapper = $objectMapper;
    }

    /**
     * @param $className
     * @return $this
     */
    public function setModelClassName($className)
    {
        $this->className = $className;
        $this->modelObject = new $className;

        return $this;
    }

    /**
     * @return \Bun\Core\Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->objectMapper->getStorage($this->className);
    }

    /**
     * @return ObjectMapperInterface
     */
    public function getObjectMapper()
    {
        return $this->objectMapper;
    }

    /**
     * @param $id
     * @return ModelInterface
     */
    abstract public function find($id);

    /**
     * @param $where
     * @param array $orderBy
     * @param array $limit
     * @return ModelInterface[]
     */
    abstract public function findBy($where, $orderBy = array(), $limit = array());

    abstract public function count($where, $limit = array());

    /**
     * @param $data
     * @return \Bun\Core\Model\ModelInterface
     */
    protected function createObject($data)
    {
        if ($data) {
            return $this->getObjectMapper()->map($this->className, $data);
        }

        return null;
    }

    /**
     * @param $data
     * @param array $aggregateFields
     * @return \Bun\Core\Model\ModelInterface[]
     */
    protected function createObjectsArray($data, $aggregateFields = array())
    {
        if ($data) {
            return $this->getObjectMapper()->mapArray($this->className, $data, $aggregateFields);
        }

        return array();
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->modelObject->getTableName();
    }

    /**
     * @param $clause
     * @param null|ModelInterface $object
     * @return array
     */
    public function mapClauseFields($clause, $object = null)
    {
        if ($object === null) {
            $object = $this->modelObject;
        }

        $mappedClause = array();
        foreach ($clause as $field => $value) {
            $mappedField = null;
            $mappedValue = null;

            if (strpos($field, '$') === false) {
                $mappedField = $object->field($field);
            }
            elseif (is_array($value)) {
                $mappedValue = $this->mapClauseFields($value, $object);
            }

            if($mappedField !== null) {
                $mappedClause[$mappedField] = $value;
            }
            elseif($mappedValue !== null) {
                $mappedClause[$field] = $mappedValue;
            }
            else {
                $mappedClause[$field] = $value;
            }
        }

        return $mappedClause;
    }

    /**
     * @param $field
     * @param null $object
     * @return mixed
     */
    public function mapField($field, $object = null)
    {
        if ($object === null) {
            $object = $this->modelObject;
        }

        return $object->field($field);
    }
}