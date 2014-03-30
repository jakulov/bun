<?php
namespace Bun\Core\Model;

/**
 * Class AbstractModel
 *
 * @package Bun\Model
 */
abstract class AbstractModel implements ModelInterface
{
    protected $objectMapperName = 'abstract';
    protected $repositoryName = 'abstract';

    protected $schema = array(
        'fields' => array(
            'id' => array(
                'map'            => 'id',
                'type'           => 'integer',
                'auto_increment' => true
            ),
        ),
    );

    protected $id;

    protected $tableName = 'abstract_model';

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return bool
     */
    public function isNewObject()
    {
        return $this->id === null;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectMapperName()
    {
        return $this->objectMapperName;
    }

    /**
     * @return string
     */
    public function getRepositoryName()
    {
        return $this->repositoryName;
    }

    /**
     * @param $fieldName
     * @return mixed
     */
    public function field($fieldName)
    {
        $fieldRest = '';
        if(strpos($fieldName, ' ')) {
            $fieldNameParts = explode(' ', $fieldName);
            $fieldName = $fieldNameParts[0];
            array_shift($fieldNameParts);
            $fieldRest = ' ' . join(' ', $fieldNameParts);
        }
        if(isset($this->schema['fields'][$fieldName])) {
            return $this->schema['fields'][$fieldName]['map'] . $fieldRest;
        }

        return $fieldName;
    }

    /**
     * @deprecated Use setter and getters instead
     * @param $data
     */
    public function __setData($data)
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }
}