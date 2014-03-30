<?php
namespace Bun\PDO\Generator;

use Bun\Core\File\File;
use Bun\Core\ObjectMapper\ObjectMapperInterface;
use Bun\PDO\PdoStorageAwareInterface;
use Bun\PDO\PdoStorage;
use Bun\Core\Model\ModelInterface;

/**
 * Class ModelGenerator
 *
 * @package Bun\PDO
 */
class ModelGenerator implements PdoStorageAwareInterface
{
    /** @var PdoStorage */
    protected $storage;

    protected $fieldTypes = array(
        'int'       => 'int',
        'tinyint'   => 'int',
        'mediumint' => 'int',
        'char'      => 'string',
        'varchar'   => 'string',
        'text'      => 'string',
        'longtext'  => 'string',
        'decimal'   => 'float',
        'datetime'  => 'datetime',
    );

    protected $contextModelName = '';

    /**
     * @param PdoStorage $pdoStorage
     */
    public function setPdoStorage(PdoStorage $pdoStorage)
    {
        $this->storage = $pdoStorage;
    }

    /**
     * @param $table
     * @param $modelName
     * @return bool|int|null
     */
    public function generateSchema($table, $modelName)
    {
        $modelFile = $this->getModelFile($modelName);
        $modelDir = dirname($modelFile);
        $modelNameParts = explode('\\', $modelName);
        $modelClass = end($modelNameParts);
        $modelNameSpace = array_slice($modelNameParts, 0, count($modelNameParts) - 1);
        $modelNameSpace = join('\\', $modelNameSpace);

        $schema = $this->getTableSchema($table);

        $use = 'Bun\\PDO\\Model\\AbstractPdoMapperModel';
        $extends = 'AbstractPdoMapperModel';
        $tab = '    ';

        $code = '<?php' . "\n";
        $code .= 'namespace ' . $modelNameSpace . ";\n\n";
        $code .= 'use ' . $use . ';' . "\n\n";

        $code .= '/**' . "\n";
        $code .= ' * Class ' . $modelClass . "\n";
        $code .= ' * ' . "\n";
        $code .= ' * @package ' . $modelNameSpace . "\n";
        $code .= ' * @generator Bun\\PDO\\Generator\\ModelGenerator' . "\n";
        $code .= ' * @date ' . date('Y-m-d H:i:s') . "\n";
        $code .= ' */' . "\n";

        $code .= 'class ' . $modelClass . ' extends ' . $extends . "\n";
        $code .= '{' . "\n";
        $code .= $tab . 'protected $tableName = \'' . $table . '\';' . "\n\n";
        $code .= $tab . 'protected $schema = ' . "\n";
        $code .= $tab . $tab . '/*schema*/' . "\n\n";

        $code .= $tab . '/*body*/' . "\n";
        $code .= '}' . "\n";

        $code = str_replace('/*schema*/', $schema, $code);

        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0777, true);
        }
        $file = new File($modelFile, true);

        return $file->setContent($code, true);
    }

    /**
     * @param $modelName
     * @return bool
     * @throws ModelGeneratorException
     */
    public function generateBody($modelName)
    {
        $modelFile = $this->getModelFile($modelName);
        $modelNameParts = explode('\\', $modelName);
        $modelClass = end($modelNameParts);
        $this->contextModelName = $modelClass;

        if (File::exists($modelFile)) {
            $file = new File($modelFile);
            if (class_exists($modelName)) {
                /** @var ModelInterface $model */
                $model = new $modelName;
                $schema = $model->getSchema();
                $fields = '';
                $methods = '';
                foreach ($schema['fields'] as $fieldName => $fieldParams) {
                    $fields .= $this->getFieldDefinition($fieldName, $fieldParams);
                    $methods .= $this->getFieldGetter($fieldName, $fieldParams);
                    $methods .= $this->getFieldSetter($fieldName, $fieldParams);
                }

                $relations = array(
                    ObjectMapperInterface::RELATION_ONE_TO_ONE,
                    ObjectMapperInterface::RELATION_ONE_TO_MANY,
                    ObjectMapperInterface::RELATION_MANY_TO_ONE,
                );

                foreach ($relations as $relation) {
                    if (isset($schema[$relation]) && !empty($schema[$relation])) {
                        foreach ($schema[$relation] as $fieldName => $fieldParams) {
                            $fields .= $this->getRelationFieldDefinition($fieldName, $fieldParams, $relation);
                            $methods .= $this->getRelationFieldGetter($fieldName, $fieldParams, $relation);
                            $methods .= $this->getRelationFieldSetter($fieldName, $fieldParams, $relation);
                            if (strpos($relation, 'ToMany') !== false) {
                                $methods .= $this->getRelationFieldAdder($fieldName, $fieldParams, $relation);
                                $methods .= $this->getRelationFieldRemover($fieldName, $fieldParams, $relation);
                            }
                        }
                    }
                }

                $body = $fields . $methods;
                $code = $file->getContent();
                $code = str_replace('/*body*/', $body, $code);

                if ($file->setContent($code, true)) {
                    return true;
                }

                throw new ModelGeneratorException('Could not write model file content: ' . $file->getFullName());
            }

            throw new ModelGeneratorException('Model class ' . $modelName . ' does not exists');
        }

        throw new ModelGeneratorException('Model file does not exists: ' . $modelFile);
    }

    /**
     * @param $fieldName
     * @param $fieldParams
     * @param $relation
     * @return string
     */
    protected function getRelationFieldDefinition($fieldName, $fieldParams, $relation)
    {
        $tab = '    ';
        $model = strpos($relation, 'ToMany') !== false ? $fieldParams['model'] . '[]' : $fieldParams['model'];
        $model = '\\' . $model;
        $type = $tab . '/** @var ' . $model . ' */' . "\n";

        return $type . $tab .
        'protected $' . $fieldName .
        ";\n";
    }

    /**
     * @param $fieldName
     * @param $fieldParams
     * @param $relation
     * @return string
     */
    protected function getRelationFieldGetter($fieldName, $fieldParams, $relation)
    {
        $tab = '    ';
        $model = strpos($relation, 'ToMany') !== false ? $fieldParams['model'] . '[]' : $fieldParams['model'];
        $model = '\\' . $model;
        $type = $tab . '/**' . "\n";
        $type .= $tab . ' * @return ' . $model . "\n";
        $type .= $tab . ' */' . "\n";

        $method = $tab . 'public function get' . ucfirst($fieldName) . "()\n";
        $method .= $tab . "{\n";
        $method .= $tab . $tab . 'return $this->' . $fieldName . ";\n";
        $method .= $tab . "}\n";

        return "\n" . $type . $method;
    }

    /**
     * @param $fieldName
     * @param $fieldParams
     * @param $relation
     * @return string
     */
    protected function getRelationFieldSetter($fieldName, $fieldParams, $relation)
    {
        $tab = '    ';
        $model = strpos($relation, 'ToMany') !== false ? $fieldParams['model'] . '[]' : $fieldParams['model'];
        $model = '\\' . $model;
        $type = $tab . '/**' . "\n";
        $type .= $tab . ' * @param $' . $fieldName . ' ' . $model . "\n";
        $type .= $tab . ' * @return $this' . "\n";
        $type .= $tab . ' */' . "\n";

        $method = $tab . 'public function set' . ucfirst($fieldName) . "($" . $fieldName . ")\n";
        $method .= $tab . "{\n";
        $method .= $tab . $tab . '$this->' . $fieldName . " = $" . $fieldName . ";\n\n";
        if (strpos($relation, 'ToMany') !== false) {
            $foreignSetter = 'set' . $this->contextModelName;
            $method .= $tab . $tab . 'if (!($' . $fieldName . ' instanceof \\Bun\\Core\\Model\\ModelArrayProxy)) {' . "\n";
            $method .= $tab . $tab . $tab . 'foreach ($' . $fieldName . ' as $_item) {' . "\n";
            $method .= $tab . $tab . $tab . $tab . '$_item->' . $foreignSetter . '($this);' . "\n";
            $method .= $tab . $tab . $tab . "}\n";
            $method .= $tab . $tab . "}\n\n";
        }
        elseif ($relation === 'oneToOne') {
            // TODO ? maybe add some foreignSetters
        }
        else {
            $foreignAdder = 'add' . $this->contextModelName;
            $method .= $tab . $tab . 'if ($' . $fieldName . ' instanceof ' . $model . ') {' . "\n";
            $method .= $tab . $tab . $tab . '$' . $fieldName . '->' . $foreignAdder . '($this);' . "\n";
            $method .= $tab . $tab . '}' . "\n\n";
        }

        $method .= $tab . $tab . 'return $this' . ";\n";
        $method .= $tab . "}\n";

        return "\n" . $type . $method;
    }

    /**
     * @param $fieldName
     * @param $fieldParams
     * @param $relation
     * @return string
     */
    protected function getRelationFieldAdder($fieldName, $fieldParams, $relation)
    {
        $model = '\\' . $fieldParams['model'];
        $paramName = explode('\\', $model);
        $paramName = lcfirst(end($paramName));
        $tab = '    ';
        $type = $tab . '/**' . "\n";
        $type .= $tab . ' * @param $' . $paramName . ' ' . $model . "\n";
        $type .= $tab . ' * @return $this' . "\n";
        $type .= $tab . ' */' . "\n";

        $foreignSetter = 'set' . $this->contextModelName;
        $method = $tab . 'public function add' . ucfirst($paramName) . "($" . $paramName . ")\n";
        $method .= $tab . "{\n";
        $method .= $tab . $tab . 'if (\\Bun\\Core\\Model\\ModelArray::contains($this->' . $fieldName . ', $' .
            $paramName . ') === false) {' . ";\n";
        $method .= $tab . $tab . $tab . '$this->' . $fieldName . '[] = $' . $paramName . ';' . "\n";
        $method .= $tab . $tab . $tab . '$' . $paramName . '->' . $foreignSetter . '($this);' . "\n";
        $method .= $tab . $tab . "}\n\n";

        $method .= $tab . $tab . 'return $this' . ";\n";
        $method .= $tab . "}\n";

        return "\n" . $type . $method;
    }

    /**
     * @param $fieldName
     * @param $fieldParams
     * @param $relation
     * @return string
     */
    protected function getRelationFieldRemover($fieldName, $fieldParams, $relation)
    {
        $model = '\\' . $fieldParams['model'];
        $paramName = explode('\\', $model);
        $paramName = lcfirst(end($paramName));
        $tab = '    ';
        $type = $tab . '/**' . "\n";
        $type .= $tab . ' * @param $' . $paramName . ' ' . $model . "\n";
        $type .= $tab . ' * @return $this' . "\n";
        $type .= $tab . ' */' . "\n";

        $method = $tab . 'public function remove' . ucfirst($paramName) . "($" . $paramName . ")\n";
        $method .= $tab . "{\n";
        $method .= $tab . $tab . '$key = \\Bun\\Core\\Model\\ModelArray::contains($this->' . $fieldName . ', $' . $paramName . ');' . "\n";
        $method .= $tab . $tab . 'if ($key !== false) {' . "\n";
        $method .= $tab . $tab . $tab . 'unset($this->' . $fieldName . '[$key]);' . "\n";
        $method .= $tab . $tab . "}\n\n";

        $method .= $tab . $tab . 'return $this' . ";\n";
        $method .= $tab . "}\n";

        return "\n" . $type . $method;
    }

    /**
     * @param $fieldName
     * @param $fieldParams
     * @return string
     */
    protected function getFieldDefinition($fieldName, $fieldParams)
    {
        $tab = '    ';
        $default = $this->getFieldDefaultValue($fieldParams);
        $type = $tab . '/** @var ' . $fieldParams['type'] . ' */' . "\n";

        return $type . $tab .
        'protected $' . $fieldName .
        ($default !== null ? " = " . $default : '') .
        ";\n";
    }

    /**
     * @param $fieldParams
     * @return null|string
     */
    protected function getFieldDefaultValue($fieldParams)
    {
        return (isset($fieldParams['default']) && $fieldParams['default'] !== null) ?
            ($fieldParams['type'] === 'string' || $fieldParams['type'] === 'datetime' ?
                "'" . $fieldParams['default'] . "'" :
                $fieldParams['default']) :
            null;
    }

    /**
     * @param $fieldName
     * @param $fieldParams
     * @return string
     */
    protected function getFieldGetter($fieldName, $fieldParams)
    {
        $tab = '    ';
        $type = $tab . '/**' . "\n";
        $type .= $tab . ' * @return ' . $fieldParams['type'] . "\n";
        $type .= $tab . ' */' . "\n";

        $method = $tab . 'public function get' . ucfirst($fieldName) . "()\n";
        $method .= $tab . "{\n";
        $method .= $tab . $tab . 'return $this->' . $fieldName . ";\n";
        $method .= $tab . "}\n";

        return "\n" . $type . $method;
    }

    /**
     * @param $fieldName
     * @param $fieldParams
     * @return string
     */
    protected function getFieldSetter($fieldName, $fieldParams)
    {
        $tab = '    ';
        $type = $tab . '/**' . "\n";
        $type .= $tab . ' * @param $' . $fieldName . ' ' . $fieldParams['type'] . "\n";
        $type .= $tab . ' * @return $this' . "\n";
        $type .= $tab . ' */' . "\n";

        $method = $tab . 'public function set' . ucfirst($fieldName) . "($" . $fieldName . ")\n";
        $method .= $tab . "{\n";
        $method .= $tab . $tab . '$this->' . $fieldName . " = $" . $fieldName . ";\n\n";
        $method .= $tab . $tab . 'return $this' . ";\n";
        $method .= $tab . "}\n";

        return "\n" . $type . $method;
    }

    /**
     * @param $table
     * @return string
     * @throws ModelGeneratorException
     */
    protected function getTableSchema($table)
    {
        $fields = $this->getTableFields($table);
        if (!$fields) {
            throw new ModelGeneratorException('Table ' . $table . ' has no fields or does not exists');
        }
        //$references = $this->getTableReferences($table);
        //$referenced = $this->getReferencedToTable($table);
        // TODO use references

        $tab = '    ';

        $schema = str_repeat($tab, 0) . 'array(' . "\n";

        $schemaFields = str_repeat($tab, 3) . '\'fields\' => array(' . "\n";
        foreach ($fields as $fieldName => $fieldSchema) {
            $fieldType = $this->getFieldType($fieldSchema['DATA_TYPE']);
            $required = $fieldSchema['IS_NULLABLE'] !== 'YES';
            $propertyName = $this->fieldToProperty($fieldName);
            $autoIncrement = $fieldSchema['EXTRA'] === 'auto_increment';
            $isId = $fieldSchema['COLUMN_KEY'] === 'PRI';
            if ($isId) {
                $propertyName = 'id';
            }
            $default = $fieldSchema['COLUMN_DEFAULT'];
            if ($default === NULL || $default === 'NULL') {
                $default = 'null';
            }
            else {
                if ($fieldType === 'string' || $fieldType === 'datetime') {
                    $default = "'" . $default . "'";
                }
            }
            $schemaFields .= str_repeat($tab, 4) . '\'' . $propertyName . '\' => array(' . "\n";
            $schemaFields .= str_repeat($tab, 5) . "'map' => '" . $fieldName . "'," . "\n";
            $schemaFields .= str_repeat($tab, 5) . "'type' => '" . $fieldType . "'," . "\n";
            $schemaFields .= str_repeat($tab, 5) . "'default' => " . $default . "," . "\n";
            if ($required) {
                $schemaFields .= str_repeat($tab, 5) . "'required' => true," . "\n";
            }
            if ($autoIncrement) {
                $schemaFields .= str_repeat($tab, 5) . "'auto_increment' => true," . "\n";
            }
            $schemaFields .= str_repeat($tab, 4) . '),' . "\n";
        }
        $schemaFields .= str_repeat($tab, 3) . '),' . "\n";

        $schema .= $schemaFields;

        $schema .= str_repeat($tab, 2) . ');';

        return $schema;
    }


    /**
     * @param $fieldName
     * @return string
     */
    protected function fieldToProperty($fieldName)
    {
        $words = str_replace('_', ' ', $fieldName);
        $ucWords = ucwords($words);

        return lcfirst(str_replace(' ', '', $ucWords));
    }

    /**
     * @param $type
     * @return mixed
     */
    protected function getFieldType($type)
    {
        if (isset($this->fieldTypes[$type])) {
            return $this->fieldTypes[$type];
        }

        return $type;
    }

    /**
     * @param $table
     * @return array
     */
    protected function getTableFields($table)
    {
        $query = $this->storage->getQueryBuilder()
            ->from('information_schema.columns')
            ->where('table_schema = database()')
            ->where('table_name = ?', $table)
            ->orderBy('ordinal_position asc');

        $result = $query->fetchAll();

        $return = array();
        if ($result) {
            foreach ($result as $row) {
                $return[$row['COLUMN_NAME']] = $row;
            }
        }

        return $return;
    }

    /**
     * @param $table
     * @return array
     */
    protected function getTableReferences($table)
    {
        $query = $this->storage->getQueryBuilder()
            ->from('information_schema.key_column_usage')
            ->where('constraint_schema=database()')
            ->where('table_schema=database()')
            ->where('referenced_table_schema=database()')
            ->where('table_name = ?', $table);

        $result = $query->fetchAll();
        $return = array();
        if ($result) {
            foreach ($result as $row) {
                $return[$row['COLUMN_NAME']] = $row;
            }
        }

        return $return;
    }

    /**
     * @param $table
     * @return array
     */
    protected function getReferencedToTable($table)
    {
        $query = $this->storage->getQueryBuilder()
            ->from('information_schema.key_column_usage')
            ->where('constraint_schema=database()')
            ->where('table_schema=database()')
            ->where('referenced_table_schema=database()')
            ->where('referenced_table_name = ?', $table);

        $result = $query->fetchAll();
        $return = array();
        foreach ($result as $row) {
            $return[$row['COLUMN_NAME']] = $row;
        }

        return $return;
    }

    /**
     * @param $modelName
     * @return string
     */
    public function getModelFile($modelName)
    {
        return SRC_DIR . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $modelName) . '.php';
    }
}