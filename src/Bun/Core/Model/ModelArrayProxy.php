<?php
namespace Bun\Core\Model;

use Bun\Core\ObjectMapper\ObjectMapperException;
use Bun\Core\ObjectMapper\ObjectMapperInterface;

/**
 * Class ModelArrayProxy
 *
 * @package Bun\Model
 */
class ModelArrayProxy extends ModelProxy implements ModelProxyInterface, \ArrayAccess, \Iterator, \Countable
{
    /** @var \Bun\Core\Model\ModelInterface */
    protected $parentModel;
    /** @var \Bun\Core\ObjectMapper\ObjectMapperInterface  */
    protected $mapper;
    /** @var string */
    protected $className;
    /** @var string */
    protected $setter;
    /** @var array  */
    protected $where = array();
    /** @var array  */
    protected $orderBy = array();
    /** @var array */
    protected $excludeRelation = array();
    /** @var ModelInterface[] */
    protected $instance = null;
    /** @var int */
    protected $iteration = 0;

    /**
     * @param ModelInterface $parentModel
     * @param ObjectMapperInterface $objectMapper
     * @param $className
     * @param $setter
     * @param array $where
     * @param array $orderBy
     * @param array $excludeRelation
     */
    public function __construct(
        ModelInterface $parentModel,
        ObjectMapperInterface $objectMapper,
        $className,
        $setter,
        $where = array(),
        $orderBy = array(),
        $excludeRelation = array()
    )
    {
        $this->parentModel = $parentModel;
        $this->mapper = $objectMapper;
        $this->className = $className;
        $this->setter = $setter;
        $this->where = $where;
        $this->orderBy = $orderBy;
        $this->excludeRelation = $excludeRelation;
    }

    public function getInstance()
    {
        if($this->instance === null) {
            $this->instance = $this->mapper
                ->mapRelationObjectsArray($this->className, $this->where, $this->orderBy, $this->excludeRelation);
            $this->setInstanceToParentModel();
        }

        return $this->instance;
    }

    /**
     * setting this instance to this parentModel
     */
    protected function setInstanceToParentModel()
    {
        call_user_func_array(
            array($this->parentModel, $this->setter),
            array($this->instance)
        );
    }

    /**
     * @param mixed $offset
     * @return ModelInterface|null
     */
    public function offsetGet($offset)
    {
        $instance = $this->getInstance();

        if(isset($instance[$offset])) {
            return $instance[$offset];
        }

        return null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \Bun\Core\ObjectMapper\ObjectMapperException
     */
    public function offsetSet($offset, $value)
    {
        if($value instanceof ModelInterface) {
            $this->getInstance();
            $this->instance[$offset] = $value;

            $this->setInstanceToParentModel();
        }

        throw new ObjectMapperException(
            'Cannot offsetSet '. $value .' to array of models not an instance of ModelInterface'
        );
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $instance = $this->getInstance();

        return isset($instance[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if($this->offsetExists($offset)) {
            unset($this->instance[$offset]);

            $this->setInstanceToParentModel();
        }
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $instance = $this->getInstance();

        return isset($instance[$this->iteration]);
    }

    /**
     * @return ModelInterface|void
     */
    public function next()
    {
        $instance = $this->getInstance();
        $this->iteration++;

        return $this->valid() ? $instance[$this->iteration] : false;
    }

    public function key()
    {
        return $this->iteration;
    }

    public function current()
    {
        $instance = $this->getInstance();

        return $instance[$this->iteration];
    }

    public function rewind()
    {
        $this->iteration = 0;
    }

    /**
     * @return int
     */
    public function count()
    {
        $instance = $this->getInstance();

        return count($instance);
    }
}