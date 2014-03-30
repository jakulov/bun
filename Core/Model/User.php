<?php
namespace Bun\Core\Model;

/**
 * Class User
 *
 * @package Bun\Core\Model
 */
class User extends AbstractFileMapperModel
{
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const GENDER_UNKNOWN = null;
    /**
     * @var array
     */
    public static $genders = array(
        self::GENDER_UNKNOWN => 'Не указан',
        self::GENDER_MALE => 'Мужской',
        self::GENDER_FEMALE => 'Женский',
    );
    /**
     * @var array
     */
    protected $schema = array(
        'fields'    => array(
            'id'      => array(
                'map'            => 'id',
                'type'           => 'int',
                'auto_increment' => true
            ),
            'name'    => array(
                'map'  => 'name',
                'type' => 'string',
            ),
            'gender' => array(
                'map' => 'sex',
                'type' => 'int',
            ),
            'groupId' => array(
                'map'  => 'group_id',
                'type' => 'int',
            ),
        ),
        'manyToOne' => array(
            'group' => array(
                'model'      => '\\Bun\\Core\\Model\\UserGroup',
                'mappedBy'   => 'groupId',
                'foreignKey' => 'id'
            )
        ),
        'oneToMany' => array(
            'post2Authors' => array(
                'model'      => '\\Bun\\Core\\Model\\Post2Author',
                'mappedBy'   => 'id',
                'foreignKey' => 'authorId'
            ),
            'createdPosts' => array(
                'model'      => '\\Bun\\Core\\Model\\Post',
                'mappedBy'   => 'id',
                'foreignKey' => 'creatorId',
            )
        )
    );

    /** @var string */
    protected $tableName = 'users';
    /** @var string */
    protected $name;
    /** @var int */
    protected $gender;
    /** @var int */
    protected $groupId;
    /** @var UserGroup */
    protected $group;
    /** @var Post2author[] */
    protected $post2Authors = array();
    /** @var Post[] */
    protected $createdPosts = array();

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name string
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * @return UserGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param UserGroup $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;
        $group->addUser($this);

        return $this;
    }

    /**
     * @return Post2author[]
     */
    public function getPost2Authors()
    {
        return $this->post2Authors;
    }

    /**
     * @param Post2Author[] $post2Authors
     * @return $this
     */
    public function setPost2Authors($post2Authors)
    {
        $this->post2Authors = $post2Authors;
        if (!($post2Authors instanceof ModelArrayProxy)) {
            foreach ($post2Authors as $post2Author) {
                $post2Author->setAuthor($this);
            }
        }

        return $this;
    }

    /**
     * @param Post2Author $post2Author
     * @return $this
     */
    public function addPost2Author(Post2Author $post2Author)
    {
        if (ModelArray::contains($this->post2Authors, $post2Author) === false) {
            $this->post2Authors[] = $post2Author;
            $post2Author->setAuthor($this);
        }

        return $this;
    }

    /**
     * @param Post2Author $post2Author
     * @return $this
     */
    public function removePost2Author(Post2Author $post2Author)
    {
        $key = ModelArray::contains($this->post2Authors, $post2Author);
        if ($key !== false) {
            unset($this->post2Authors[$key]);
        }

        return $this;
    }

    /**
     * @return Post[]
     */
    public function getPosts()
    {
        $posts = array();
        foreach ($this->getPost2Authors() as $post2Author) {
            $posts[] = $post2Author->getPost();
        }

        return $posts;
    }

    /**
     * @return Post[]
     */
    public function getCreatedPosts()
    {
        return $this->createdPosts;
    }

    /**
     * @param Post[] $createdPosts
     * @return $this
     */
    public function setCreatedPosts($createdPosts)
    {
        $this->createdPosts = $createdPosts;

        if (!($createdPosts instanceof ModelArrayProxy)) {
            foreach ($createdPosts as $post) {
                $post->setCreator($this);
            }
        }

        return $this;
    }

    /**
     * @param Post $post
     * @return $this
     */
    public function addCreatedPost(Post $post)
    {
        if (ModelArray::contains($this->createdPosts, $post) === false) {
            $this->createdPosts[] = $post;
            $post->setCreator($this);
        }

        return $this;
    }

    /**
     * @param Post $post
     * @return $this
     */
    public function removeCreatedPost(Post $post)
    {
        $key = ModelArray::contains($this->createdPosts, $post);
        if ($key !== false) {
            unset($this->createdPosts[$key]);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getGenderName()
    {
        return self::$genders[$this->gender];
    }

    /**
     * @param int $gender
     */
    public function setGender($gender)
    {
        $this->gender = (int)$gender;
    }
}