<?php
namespace Bun\Core\Model;

use Bun\Core\Module\ModuleInterface;
use Bun\Core\ObjectMapper\ObjectMapperInterface;

/**
 * Class ModelProxy
 *
 * @package Bun\Model
 */
class ModelProxy implements ModelProxyInterface, ModelInterface
{
    /** @var \Bun\Core\Model\ModelInterface */
    protected $parentModel;
    /** @var \Bun\Core\ObjectMapper\ObjectMapperInterface */
    protected $mapper;
    /** @var string */
    protected $className;
    /** @var string */
    protected $setter;
    /** @var array */
    protected $where = array();
    /** @var array */
    protected $excludeRelation = array();
    /** @var ModelInterface */
    protected $instance;

    /**
     * @param ModelInterface $parentModel
     * @param ObjectMapperInterface $objectMapper
     * @param $className
     * @param $setter
     * @param array $where
     * @param array $excludeRelation
     */
    public function __construct(
        ModelInterface $parentModel,
        ObjectMapperInterface $objectMapper,
        $className,
        $setter,
        $where = array(),
        $excludeRelation = array()
    ) {
        $this->parentModel = $parentModel;
        $this->mapper = $objectMapper;
        $this->className = $className;
        $this->setter = $setter;
        $this->where = $where;
        $this->excludeRelation = $excludeRelation;
    }

    public function getInstance()
    {
        if ($this->instance === null) {
            $this->instance = $this->mapper->mapRelationObject($this->className, $this->where, $this->excludeRelation);
        }

        return $this->instance;
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        $instance = $this->getInstance();
        if (!($instance instanceof ModelInterface)) {
            return null;
        }

        if (strpos($method, 'set') === 0) {
            return $this->callSetter($method, $params);
        }
        elseif (strpos($method, 'get') === 0) {
            return $this->callGetter($method, $params);
        }

        return null;
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    protected function callGetter($method, $params)
    {
        return call_user_func_array(
            array($this->getInstance(), $method),
            $params
        );
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    protected function callSetter($method, $params)
    {
        $instance = $this->getInstance();
        call_user_func_array(
            array($this->parentModel, $this->setter),
            array($instance)
        );

        return call_user_func_array(
            array($instance, $method),
            $params
        );
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->getInstance()->getTableName();
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        $instance = $this->getInstance();
        if ($instance instanceof ModelInterface) {
            return $this->getInstance()->getSchema();
        }

        return array();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        $instance = $this->getInstance();
        if ($instance instanceof ModelInterface) {
            return $this->getInstance()->getId();
        }

        return null;
    }

    /**
     * @param $id
     * @return $this|mixed
     */
    public function setId($id)
    {
        $instance = $this->getInstance();
        if ($instance instanceof ModelInterface) {
            return $this->callSetter('setId', array($id));
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isNewObject()
    {
        $instance = $this->getInstance();
        if ($instance instanceof ModelInterface) {
            return $this->getInstance()->isNewObject();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getObjectMapperName()
    {
        $instance = $this->getInstance();
        if ($instance instanceof ModelInterface) {
            return $this->getInstance()->getObjectMapperName();
        }

        return null;
    }

    /**
     * @param $fieldName
     * @return mixed
     */
    public function field($fieldName)
    {
        $instance = $this->getInstance();
        if ($instance instanceof ModelInterface) {
            return $this->getInstance()->field($fieldName);
        }

        return $fieldName;
    }

    /**
     * @return mixed
     */
    public function getRepositoryName()
    {
        $instance = $this->getInstance();
        if ($instance instanceof ModelInterface) {
            return $this->getInstance()->getRepositoryName();
        }

        return null;
    }
}