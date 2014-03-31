<?php
namespace Bun\Core\Repository;

/**
 * Class UserRepository
 *
 * @package Bun\Core\Repository
 */
class UserRepository extends CoreRepository
{
    /**
     * @param $name
     * @param array $orderBy
     * @param array $limit
     * @param bool $caseSensitive
     * @return \Bun\Core\Model\ModelInterface[]
     */
    public function findByName($name, $orderBy = array(), $limit = array(), $caseSensitive = false)
    {
        $like = $caseSensitive ? '$like' : '$ilike';
        $data = $this->getStorage()
            ->table($this->getTable())
            ->findBy(
                array(
                    'name' => array($like => $name)
                ),
                $orderBy,
                $limit
            );

        return $this->createObjectsArray($data);
    }
}