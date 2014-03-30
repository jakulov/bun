<?php
namespace Bun\PDO\Repository;

use Bun\Core\Repository\AbstractRepository;
use Bun\PDO\PdoStorageException;

/**
 * Class PdoRepository
 *
 * @package Bun\PDO\Repository
 */
class PdoRepository extends AbstractRepository
{
    /**
     * @param $id
     * @return \Bun\Core\Model\ModelInterface|null
     */
    public function find($id)
    {
        $data = $this->findData($id);
        if ($data) {
            return $this->createObject($data);
        }

        return null;
    }

    /**
     * @param $id
     * @return mixed
     */
    protected function findData($id)
    {
        $where = array('id' => $id);
        $mappedWhere = $this->mapClauseFields($where);
        $query = $this->getStorage()->getQueryBuilder()
            ->from($this->getTable());

        foreach ($mappedWhere as $clause => $value) {
            if(strpos($clause, ' ') === false) {
                $clause .= ' =';
            }
            $query->where($clause . ' ?', $value);
        }

        return $query->fetch();
    }


    /**
     * @param $where
     * @param array $orderBy
     * @param array $limit
     * @return \Bun\Core\Model\ModelInterface[]
     */
    public function findBy($where, $orderBy = array(), $limit = array())
    {
        $data = $this->findDataBy($where, $orderBy, $limit);

        return $this->createObjectsArray($data);
    }

    /**
     * @param $where
     * @param array $orderBy
     * @param array $limit
     * @return array
     * @throws \Bun\PDO\PdoStorageException
     */
    protected function findDataBy($where, $orderBy = array(), $limit = array())
    {
        $mappedWhere = $this->mapClauseFields($where);

        $query = $this->getStorage()->getQueryBuilder()
            ->from($this->getTable());

        foreach ($mappedWhere as $clause => $value) {
            if(strpos($clause, ' ') === false && !is_array($value)) {
                $clause .= ' = ?';
            }
            $query->where($clause, $value);
        }

        foreach ($orderBy as $field => $sort) {
            $mapField = $this->mapField($field);
            $order = is_numeric($sort) ?
                (($sort > 0) ?
                    'ASC' :
                    'DESC') :
                $sort;
            $query->orderBy($mapField . ' ' . $order);
        }

        if (count($limit) === 2) {
            $query->offset($limit[0]);
            $query->limit($limit[1]);
        }

        try {
            return $query->fetchAll();
        }
        catch(\PDOException $e) {
            throw new PdoStorageException('Query: '. $query->getQuery() . ' caused error: '. $e->getMessage());
        }
    }

    /**
     * @param $where
     * @param array $limit
     * @return int
     */
    public function count($where, $limit = array())
    {
        $mappedWhere = $this->mapClauseFields($where);

        $query = $this->getStorage()->getQueryBuilder()
            ->from($this->getTable())->select('count(1) as count');

        foreach ($mappedWhere as $clause => $value) {
            if(strpos($clause, ' ') === false && !is_array($value)) {
                $clause .= ' = ?';
            }
            $query->where($clause, $value);
        }

        if (count($limit) === 2) {
            $query->offset($limit[0]);
            $query->limit($limit[1]);
        }

        return (int)$query->fetch('count');
    }

    /**
     * @return \Bun\PDO\PdoStorage
     */
    public function getStorage()
    {
        return $this->objectMapper->getStorage($this->className);
    }
}