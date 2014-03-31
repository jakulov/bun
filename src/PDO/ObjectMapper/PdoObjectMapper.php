<?php
namespace Bun\PDO\ObjectMapper;

use Bun\Core\ObjectMapper\AbstractObjectMapper;
use Bun\Core\Repository\RepositoryInterface;
use Bun\PDO\PdoStorage;

/**
 * Class PdoObjectMapper
 *
 * @package Bun\PDO\ObjectMapper
 */
class PdoObjectMapper extends AbstractObjectMapper
{
    protected $storageServiceName = 'bun.pdo.storage';

    /**
     * @param $className
     * @param $where
     * @param array $excludeRelation
     * @return \Bun\Core\Model\ModelInterface|null
     */
    public function mapRelationObject($className, $where, $excludeRelation = array())
    {
        $relationObject = $this->getModelObject($className);
        $repositoryName = $relationObject->getRepositoryName();
        /** @var RepositoryInterface $objectRepository */
        $objectRepository = new $repositoryName;
        $objectRepository->setModelClassName($className);
        $objectRepository->setObjectManager($this);

        $objects = $objectRepository->findBy($where, array(), array(0, 1));

        return $objects ? $objects[0] : null;
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

        $query = $this->getStorage($className)->getQueryBuilder()
            ->from($relationObject->getTableName());
        foreach ($where as $field => $value) {
            $query->where($relationObject->field($field) . ' = ?', $value);
        }

        if ($orderBy) {
            $query->orderBy(join(', ', $orderBy));
        }

        $data = $query->fetchAll();

        return $this->mapArray($className, $data, array(), $excludeRelation);
    }

    /**
     * @param $className
     * @return PdoStorage
     */
    public function getStorage($className)
    {
        return parent::getStorage($className);
    }
}