<?php
namespace Bun\Core\Repository;

/**
 * Class CoreRepository
 *
 * @package Bun\Core\Repository
 */
class CoreRepository extends AbstractRepository
{
    /**
     * @param $id
     * @return \Bun\Core\Model\ModelInterface
     */
    public function find($id)
    {
        $data = $this->getStorage()
            ->table($this->getTable())
            ->find($id);

        return $this->createObject($data);
    }

    /**
     * @param $where
     * @param array $orderBy
     * @param array $limit
     * @return \Bun\Core\Model\ModelInterface[]
     */
    public function findBy($where, $orderBy = array(), $limit = array())
    {
        $data = $this->getStorage()
            ->table($this->getTable())
            ->findBy($this->mapClauseFields($where), $this->mapClauseFields($orderBy), $limit, false);

        return $this->createObjectsArray($data);
    }

    /**
     * @param $where
     * @param array $limit
     * @return int
     */
    public function count($where, $limit = array())
    {
        $data = $this->getStorage()
            ->table($this->getTable())
            ->findBy($this->mapClauseFields($where), array(), $limit, false);

        return count($data);
    }

}