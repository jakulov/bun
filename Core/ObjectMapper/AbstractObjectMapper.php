<?php
namespace Bun\Core\ObjectMapper;

use Bun\Core\Container\ContainerInterface;
use Bun\Core\Container\ContainerAwareInterface;
use Bun\Core\Config\ConfigInterface;
use Bun\Core\Config\ConfigAwareInterface;
use Bun\Core\Model\ModelArrayProxy;
use Bun\Core\Model\ModelInterface;
use Bun\Core\Model\ModelProxy;
use Bun\Core\Storage\StorageInterface;

/**
 * Class ObjectMapper
 *
 * @package Bun\Core\ObjectMapper
 */
abstract class AbstractObjectMapper implements ObjectMapperInterface, ConfigAwareInterface, ContainerAwareInterface
{
    protected $modelInterface = 'Bun\\Core\\Model\\ModelInterface';
    /** @var ConfigInterface */
    protected $config;
    /** @var ContainerInterface */
    protected $container;
    /** @var string */
    protected $storageServiceName = 'abstract';
    /** @var StorageInterface */
    protected $storage;
    /** @var ModelInterface[] */
    protected $cachedSchemaObjects = array();
    /** @var ModelInterface[] */
    protected $mappedObjects = array();

    protected $relationSchemas = array(
        self::RELATION_ONE_TO_ONE,
        self::RELATION_ONE_TO_MANY,
        self::RELATION_MANY_TO_ONE,
        //self::RELATION_MANY_TO_MANY, TODO: enable mapping MANY_TO_MANY
    );

    /**
     * @param $object
     * @return array|ModelInterface
     * @throws ObjectMapperException
     */
    public function save($object)
    {
        if ($object instanceof ModelInterface) {
            return $this->saveObject($object);
        }
        elseif (is_array($object)) {
            $savedObjects = array();
            foreach ($object as $objectItem) {
                if ($objectItem instanceof ModelInterface) {
                    $savedObjects[] = $this->saveObject($objectItem);
                }
                else {
                    throw new ObjectMapperException('Cannot map not an instance of ' . $this->modelInterface);
                }
            }

            return $savedObjects;
        }

        throw new ObjectMapperException('Cannot map not an instance of ' . $this->modelInterface);
    }

    /**
     * @param $className
     * @param $data
     * @param array $excludeRelation
     * @return ModelInterface
     * @throws ObjectMapperException
     */
    public function map($className, $data, $excludeRelation = array())
    {
        $object = new $className;
        if ($object instanceof ModelInterface) {
            $object = $this->arrayToObject($object, $data, $excludeRelation);
            $this->addMappedObjectCopy($object);

            return $object;
        }

        throw new ObjectMapperException('Mapping object ' . $className . ' should implements ' . $this->modelInterface);

        //throw new ObjectMapperException('Cannot map not existing class ' . $className);
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
        $objects = array();
        foreach ($data as $objectData) {
            $objects[] = $this->map($className, $objectData, $excludeRelation);
        }

        if ($objects) {
            foreach ($aggregateRelations as $aggregateField) {
                $objects = $this->aggregateObjectsArrayRelation($objects, $aggregateField, $excludeRelation);
            }
        }

        foreach ($objects as $object) {
            $this->addMappedObjectCopy($object);
        }

        return $objects;
    }

    /**
     * @param \Bun\Core\Model\ModelInterface[] $objects
     * @param $aggregateField
     * @param $excludeRelation
     * @return mixed
     */
    protected function aggregateObjectsArrayRelation($objects, $aggregateField, $excludeRelation)
    {
        $fooObject = $objects[0];
        $schema = $fooObject->getSchema();
        $aggregateFieldParams = null;
        foreach ($schema as $schemaPart => $schemaPartParams) {
            if ($schemaPart !== self::SCHEMA_PART_FIELDS) {
                foreach ($schemaPartParams as $schemaField => $fieldParams) {
                    if ($schemaField === $aggregateField) {
                        if (
                            !$excludeRelation ||
                            (
                                $excludeRelation['model'] !== $fieldParams['model'] &&
                                $excludeRelation['foreignKey'] !== $fieldParams['foreignKey']
                            )
                        ) {
                            $aggregateFieldParams = $fieldParams;
                        }
                    }
                }
            }
        }

        if ($aggregateFieldParams !== null) {

            $objectToAggregate = array();
            $foreignField = $aggregateFieldParams['foreignKey'];
            /** @var ModelInterface $foreignObject */
            $foreignObject = new $aggregateFieldParams['model'];
            $foreignField = $foreignObject->field($foreignField);

            $foreignFieldValueGetter = 'get' . ucfirst($aggregateFieldParams['mappedBy']);

            $foreignFieldValue = array();
            foreach ($objects as $object) {
                $objectToAggregate[$object->getId()] = array();
                $foreignFieldValue[] = $object->$foreignFieldValueGetter();
            }

            $where = array(
                $foreignField => array_values($foreignFieldValue)
            );

            $data = $this->getStorage(get_class($fooObject))
                ->table($foreignObject->getTableName())
                ->findBy(
                    $where,
                    isset($aggregateFieldParams['orderBy']) ? $aggregateFieldParams['orderBy'] : array()
                );

            if($data) {
                $exclude = array(
                    'model'      => get_class($fooObject),
                    'foreignKey' => $aggregateFieldParams['mappedBy']
                );
                $aggregateObjects = $this->mapArray($aggregateFieldParams['model'], $data, array(), $exclude);

                $foreignGetter = 'get' . ucfirst($aggregateFieldParams['foreignKey']);
                foreach ($aggregateObjects as $aggregateObject) {
                    $index = $aggregateObject->$foreignGetter();
                    $objectToAggregate[$index][] = $aggregateObject;
                }

                //$aggregateSetter = 'set' . ucfirst($aggregateField); // it was to slooowww
                $aggregateSetter = '__setData';

                foreach ($objects as $object) {
                    call_user_func_array(
                        array($object, $aggregateSetter),
                        array(array($aggregateField => $objectToAggregate[$object->getId()]))
                    );
                }
            }
        }

        return $objects;
    }

    /**
     * @param $className
     * @param $where
     * @param array $excludeRelation
     * @return ModelInterface
     */
    public function mapRelationObject($className, $where, $excludeRelation = array())
    {
        $relationObject = $this->getModelObject($className);

        $data = $this->getStorage($className)
            ->table($relationObject->getTableName())
            ->findBy($where, array(), array(0, 1), true);

        foreach ($data as $objectData) {
            return $this->map($className, $objectData, $excludeRelation);
        }

        return null;
    }

    /**
     * @param $className
     * @param $where
     * @param array $orderBy
     * @param array $excludeRelation
     * @return \Bun\Core\Model\ModelInterface[]
     */
    public function mapRelationObjectsArray($className, $where, $orderBy = array(), $excludeRelation = array())
    {
        $relationObject = $this->getModelObject($className);
        $data = $this->getStorage($className)
            ->table($relationObject->getTableName())
            ->findBy($where, $orderBy, array(), true);

        return $this->mapArray($className, $data, array(), $excludeRelation);
    }

    /**
     * What can be seen cannot be unseen
     *
     * @param ModelInterface $object
     * @param $data
     * @param array $excludeRelation
     * @return ModelInterface
     */
    public function arrayToObject(ModelInterface $object, $data, $excludeRelation = array())
    {
        $modelSchema = $object->getSchema();
        foreach ($object->getSchema() as $schemaPart => $schemaParams) {
            if ($schemaPart === self::SCHEMA_PART_FIELDS) {
                // mapping regular fields to array
                $setData = array();
                foreach ($schemaParams as $field => $fieldParams) {
                    $dataField = $fieldParams['map'];
                    //$setter = 'set' . ucfirst($field);
                    if (isset($data[$dataField])) {
                        $value = $data[$dataField];
                        if($fieldParams['type'] === 'int') {
                            $value = (int)$value;
                        }
                        elseif($fieldParams['type'] === 'float') {
                            $value = (float)$value;
                        }
                        // you soo sloooooooowww
                        //call_user_func_array(array($object, $setter), array($value));
                        $setData[$field] = $value;
                    }
                }
                call_user_func_array(array($object, '__setData'), array($setData));
            }
            else {
                // mapping relations proxies
                switch ($schemaPart) {
                    case self::RELATION_MANY_TO_ONE :
                    case self::RELATION_ONE_TO_ONE :
                        // mapping *ToOne relations to ModelProxy
                        $object = $this->aggregateProxyRelationToOne(
                            $object,
                            $data,
                            $schemaParams,
                            $modelSchema,
                            $excludeRelation
                        );
                        break;
                    case self::RELATION_MANY_TO_MANY :
                    case self::RELATION_ONE_TO_MANY :
                        // mapping *ToMany relations to ModelArrayProxy
                        $object = $this->aggregateProxyArrayRelationsToMany(
                            $object,
                            $data,
                            $schemaParams,
                            $modelSchema,
                            $excludeRelation
                        );
                        break;
                }
            }
        }

        return $object;
    }

    /**
     * @param ModelInterface $object
     * @param $data
     * @param $schemaParams
     * @param $modelSchema
     * @param $excludeRelation
     * @return ModelInterface
     */
    protected function aggregateProxyRelationToOne(ModelInterface $object, $data, $schemaParams, $modelSchema, $excludeRelation)
    {
        foreach ($schemaParams as $field => $fieldParams) {
            if (
                !$excludeRelation || (
                    $excludeRelation['model'] !== $fieldParams['model'] ||
                    $excludeRelation['foreignKey'] !== $fieldParams['foreignKey']
                )
            ) {
                $dataField = $modelSchema[self::SCHEMA_PART_FIELDS][$fieldParams['mappedBy']]['map'];
                $relationWhere = array(
                    $fieldParams['foreignKey'] => $data[$dataField]
                );
                $exclude = array(
                    'model'      => get_class($object),
                    'foreignKey' => $fieldParams['mappedBy']
                );
                // creation ModelProxy with relation params
                $relationProxy = new ModelProxy(
                    $object, // parent object to set real Model Instance in future
                    $this, // objectMapper instance to fetch real Model
                    $fieldParams['model'], // className to know which model to fetch
                    'set' . ucfirst($field), // parent object setter to set Model Instance
                    $relationWhere, // relation clause to fetch needed objects
                    $exclude // exclude recursion relation mapping
                );

                // setting ModelProxy to relation field
                $relationSetter = 'set' . ucfirst($field);
                call_user_func_array(array($object, $relationSetter), array($relationProxy));
            }
        }

        return $object;
    }

    /**
     * @param ModelInterface $object
     * @param $data
     * @param $schemaParams
     * @param $modelSchema
     * @param $excludeRelation
     * @return ModelInterface
     */
    protected function aggregateProxyArrayRelationsToMany(ModelInterface $object, $data, $schemaParams, $modelSchema, $excludeRelation)
    {
        foreach ($schemaParams as $field => $fieldParams) {
            if (
                !$excludeRelation ||
                (
                    $excludeRelation['model'] !== $fieldParams['model'] ||
                    $excludeRelation['foreignKey'] !== $fieldParams['foreignKey']
                )
            ) {
                $relationObject = $this->getModelObject($fieldParams['model']);
                $relationSchema = $relationObject->getSchema();
                $whereField = $relationSchema[self::SCHEMA_PART_FIELDS][$fieldParams['foreignKey']]['map'];
                $dataField = $modelSchema[self::SCHEMA_PART_FIELDS][$fieldParams['mappedBy']]['map'];
                $where = array(
                    $whereField => $data[$dataField]
                );
                $exclude = array(
                    'model'      => get_class($object),
                    'foreignKey' => $fieldParams['mappedBy']
                );
                $modelArrayProxy = new ModelArrayProxy(
                    $object,
                    $this,
                    $fieldParams['model'],
                    'set' . ucfirst($field),
                    $where,
                    isset($fieldParams['orderBy']) ? $fieldParams['orderBy'] : array(),
                    $exclude
                );

                $relationSetter = 'set' . ucfirst($field);
                call_user_func_array(array($object, $relationSetter), array($modelArrayProxy));
            }
        }

        return $object;
    }

    /**
     * @param ModelInterface $object
     * @param array $excludeRelation
     * @return ModelInterface
     * @throws ObjectMapperException
     */
    protected function saveObject(ModelInterface $object, $excludeRelation = array())
    {
        if ($this->objectNeedsSave($object)) {
            $objectData = $this->objectToData($object, $excludeRelation);
            try {
                if ($object->isNewObject()) {
                    $objectId = $this->getStorage(get_class($object))
                        ->table($object->getTableName())
                        ->insert($objectData['object']);

                    if ($objectId) {
                        $object->setId($objectId);
                    }
                }
                else {
                    $this->getStorage(get_class($object))
                        ->table($object->getTableName())
                        ->update($objectData['object'], array('id' => $object->getId()));
                }
            }
            catch(\PDOException $e) {
                throw new ObjectMapperException('Unable to save data '. $e->getMessage());
            }

            $this->saveRelationToManyObjects($object, $objectData);
        }
        $this->addMappedObjectCopy($object);

        return $object;
    }

    /**
     * @param ModelInterface $object
     * @param $objectData
     */
    protected function saveRelationToManyObjects(ModelInterface $object, $objectData)
    {
        foreach ($object->getSchema() as $schemaPart => $schemaParams) {
            if (strpos($schemaPart, 'ToMany') !== false) {
                foreach ($objectData[$schemaPart] as $field => $relationObjects) {
                    /** @var $relationObject ModelInterface */
                    foreach ($relationObjects as $relationObject) {
                        $exclude = array(
                            'mode'       => get_class($object),
                            'foreignKey' => $schemaParams[$field]['mappedBy']
                        );
                        $relationGetter = 'get' . ucfirst($schemaParams[$field]['mappedBy']);
                        $mappedBy = $object->$relationGetter();
                        $relationSetter = 'set' . ucfirst($schemaParams[$field]['foreignKey']);
                        call_user_func_array(array($relationObject, $relationSetter), array($mappedBy));
                        $this->saveObject($relationObject, $exclude);
                    }
                }
            }
        }
    }

    /**
     * @param ModelInterface $object
     * @param array $excludeRelation
     * @return array
     */
    public function objectToData(ModelInterface $object, $excludeRelation = array())
    {
        $data = array(
            'object'                   => array(),
            self::RELATION_ONE_TO_MANY => array()
        );
        $objectSchema = $object->getSchema();
        foreach ($object->getSchema() as $schemaPart => $schemaParams) {
            if ($schemaPart === self::SCHEMA_PART_FIELDS) {
                foreach ($schemaParams as $field => $fieldParams) {
                    $getter = 'get' . ucfirst($field);
                    $dataField = $fieldParams['map'];
                    $data['object'][$dataField] = $object->$getter();
                }
            }
            elseif (in_array($schemaPart, $this->relationSchemas)) {
                // relation mapping
                switch ($schemaPart) {
                    case self::RELATION_ONE_TO_ONE :
                    case self::RELATION_MANY_TO_ONE :
                        // mapping *_to_one relations
                        foreach ($schemaParams as $field => $fieldParams) {
                            $fieldData = $this->getAggregateObjectFieldData(
                                $object,
                                $field,
                                $fieldParams,
                                $excludeRelation
                            );
                            if ($fieldData !== null) {
                                // if we have relation object data
                                $dataField = $objectSchema[self::SCHEMA_PART_FIELDS][$fieldParams['mappedBy']]['map'];
                                $data['object'][$dataField] = $fieldData;
                                $objectSetter = 'set' . ucfirst($fieldParams['mappedBy']);
                                // setting data in mappedBy field
                                call_user_func_array(array($object, $objectSetter), array($fieldData));
                            }
                        }
                        break;
                    case self::RELATION_ONE_TO_MANY :
                        // mapping *_to_many relations
                        foreach ($schemaParams as $field => $fieldParams) {
                            $getter = 'get' . ucfirst($field);
                            /** @var ModelInterface[] $relatedObjects */
                            $relatedObjects = $object->$getter();
                            if ($relatedObjects && !($relatedObjects instanceof ModelArrayProxy)) {
                                foreach ($relatedObjects as $relatedObject) {
                                    if ($this->objectNeedsSave($relatedObject)) {
                                        $data[self::RELATION_ONE_TO_MANY][$field][] = $relatedObject;
                                    }
                                }
                            }
                        }
                        break;
                    case self::RELATION_MANY_TO_MANY :
                        // TODO: map array of relation objects
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * @param ModelInterface $object
     * @param $field
     * @param $fieldParams
     * @param array $excludeRelation
     * @return null|mixed
     */
    protected function getAggregateObjectFieldData(ModelInterface $object, $field, $fieldParams, $excludeRelation = array())
    {
        $getter = 'get' . ucfirst($field);
        $fieldValue = $object->$getter();
        $data = null;
        if ($fieldValue instanceof ModelInterface) {
            if (
                $fieldValue->isNewObject() &&
                (
                    !$excludeRelation ||
                    (
                        $excludeRelation['model'] !== $fieldParams['model'] ||
                        $excludeRelation['foreignKey'] !== $fieldParams['foreignKey']
                    )
                )
            ) {
                $exclude = array(
                    'model'      => get_class($object),
                    'foreignKey' => $fieldParams['mappedBy']
                );
                $fieldValue = $this->saveObject($fieldValue, $exclude);
                $setter = 'set' . ucfirst($field);
                $relationGetter = 'get' . ucfirst($fieldParams['foreignKey']);
                call_user_func_array(array($object, $setter), array($fieldValue));
                $data = $fieldValue->$relationGetter();
            }
            else {
                $relationGetter = 'get' . ucfirst($fieldParams['foreignKey']);
                $data = $object->$getter()->$relationGetter();
            }
        }
        else {
            $data = $fieldValue;
        }

        return $data;
    }

    /**
     * @param ModelInterface $object
     * @return bool
     * @throws ObjectMapperException
     */
    public function objectNeedsSave(ModelInterface $object)
    {
        if (!$object->isNewObject()) {
            $mappedCopyObject = $this->getMappedObjectCopy($object);
            if ($mappedCopyObject instanceof ModelInterface) {
                foreach ($this->getObjectFieldGetters($object) as $getter) {
                    $mappedValue = $mappedCopyObject->$getter();
                    $objectValue = $object->$getter();
                    if (!is_object($objectValue)) {
                        if (is_array($objectValue)) {
                            if ($this->relationArrayOfObjectsNeedsSave($objectValue, $mappedValue)) {
                                return true;
                            }
                        }
                        else {
                            // compare simple fields
                            if ($mappedValue !== $objectValue) {
                                return true;
                            }
                        }
                    }
                    else {
                        // if object value is relation object
                        if ($objectValue instanceof ModelInterface) {
                            // if it's mapped relation object
                            if ($mappedValue instanceof ModelProxy) {
                                // if relation object was not mapped
                                return true;
                            }
                            elseif ($mappedValue instanceof ModelInterface) {
                                if ($this->objectNeedsSave($objectValue)) {
                                    // if mapped object was changed
                                    return true;
                                }
                            }
                            else {
                                // anyway relation object needs save
                                return true;
                            }
                        }
                        elseif (!($objectValue instanceof ModelProxy)) {
                            throw new ObjectMapperException(
                                'Unable to map aggregate object: class ' . get_class($objectValue) .
                                ' should implements ' . $this->modelInterface
                            );
                        }
                    }
                }
            }

            return false;
        }

        if ($object instanceof ModelProxy) {
            return false;
        }

        return true;
    }

    /**
     * @param $objectValue
     * @param $mappedValue
     * @return bool
     * @throws ObjectMapperException
     */
    protected function relationArrayOfObjectsNeedsSave($objectValue, $mappedValue)
    {
        // mapping relation object's array
        if ($mappedValue instanceof ModelArrayProxy) {
            // if relation was not mapped
            return true;
        }
        elseif (is_array($mappedValue)) {
            // if relation was mapped checking each mapped object
            foreach ($objectValue as $objectRelation) {
                if ($objectRelation instanceof ModelInterface) {
                    if ($this->objectNeedsSave($objectRelation)) {
                        // if on of mapped objects was changed
                        return true;
                    }
                }
                else {
                    throw new ObjectMapperException(
                        'Cannot map array values that not implements ' .
                        $this->modelInterface . ''
                    );
                }
            }
        }
        else {
            throw new ObjectMapperException(
                'Cannot map array values that not implements ' .
                $this->modelInterface
            );
        }

        return false;
    }

    /**
     * Gets object field getters names
     *
     * @param ModelInterface $object
     * @return array
     * @throws ObjectMapperException
     */
    protected function getObjectFieldGetters(ModelInterface $object)
    {
        $getters = array();
        foreach ($object->getSchema() as $schemaPart => $schemaParams) {
            if (
                $schemaPart === self::SCHEMA_PART_FIELDS ||
                in_array($schemaPart, $this->relationSchemas)
            ) {
                foreach ($schemaParams as $field => $fieldParams) {
                    $getters[] = 'get' . ucfirst($field);
                }
            }
            else {
                throw new ObjectMapperException('Unknown schema definition part: ' . $schemaPart);
            }
        }

        return $getters;
    }

    /**
     * Gets mapped copy of object
     *
     * @param ModelInterface $object
     * @return null|ModelInterface
     */
    public function getMappedObjectCopy(ModelInterface $object)
    {
        $objectIdHash = $this->getObjectIdHash($object);
        if (isset($this->mappedObjects[$objectIdHash])) {
            return $this->mappedObjects[$objectIdHash];
        }

        return null;
    }

    /**
     * @param ModelInterface $object
     */
    protected function addMappedObjectCopy(ModelInterface $object)
    {
        $objectIdHash = $this->getObjectIdHash($object);
        $copy = clone $object;
        $this->mappedObjects[$objectIdHash] = $copy;
    }

    /**
     * Gets object id hash
     *
     * @param ModelInterface $object
     * @return string
     */
    protected function getObjectIdHash(ModelInterface $object)
    {
        return md5(get_class($object) . ':' . $object->getId());
    }

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
     * @param $className
     * @return StorageInterface
     */
    public function getStorage($className)
    {
        if ($this->storage === null) {
            $this->storage = $this->container->get($this->storageServiceName);
        }

        return $this->storage;
    }

    /**
     * @param $className
     * @return ModelInterface
     * @throws ObjectMapperException
     */
    protected function getModelObject($className)
    {
        if (!isset($this->cachedSchemaObjects[$className])) {
            $object = new $className;
            if ($object instanceof ModelInterface) {
                $this->cachedSchemaObjects[$className] = $object;
            }
            else {
                throw new ObjectMapperException(
                    'Cannot get schema for non ' . $this->modelInterface . ' instance of ' . $className
                );
            }
        }

        return $this->cachedSchemaObjects[$className];
    }

    /**
     * @param ModelInterface $object
     * @return bool|int
     */
    public function remove($object)
    {
        if(!$object->isNewObject()) {
            return $this->getStorage(get_class($object))
                ->table($object->getTableName())
                ->delete(
                    array('id' => $object->getId()
                )
            );
        }

        unset($object);

        return true;
    }
}