<?php
namespace Bun\Core\Model;
/**
 * Class UserGroup
 *
 * @package Bun\Model
 */
class UserGroup extends AbstractFileMapperModel
{
    protected $schema = array(
        'fields'    => array(
            'id'   => array(
                'map'            => 'id',
                'type'           => 'integer',
                'auto_increment' => true
            ),
            'name' => array(
                'map'  => 'name',
                'type' => 'string',
            ),
        ),
        'oneToMany' => array(
            'users' => array(
                'model'        => '\\Bun\\Core\\Model\\User',
                'mappedBy'     => 'id',
                'foreignKey'   => 'groupId',
                'orderBy'      => array('id' => 1),
            )
        )
    );

    protected $tableName = 'user_groups';

    protected $id;
    protected $name;
    /** @var User[] */
    protected $users = array();

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     * @return $this
     */
    public function setUsers($users)
    {
        $this->users = $users;
        if(!($users instanceof ModelArrayProxy)) {
            foreach ($users as $user) {
                $user->setGroup($this);
            }
        }

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addUser(User $user)
    {
        if (ModelArray::contains($this->users, $user) === false) {
            $this->users[] = $user;
            $user->setGroup($this);
        }

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function removeUser(User $user)
    {
        $foundKey = ModelArray::contains($this->users, $user);
        if ($foundKey !== false) {
            unset($this->users[$foundKey]);
        }

        return $this;
    }
}