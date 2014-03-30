<?php
namespace Bun\Core\Model;

/**
 * Class Post
 *
 * @package Bun\Core\Model
 */
class Post extends AbstractModel
{
    protected $tableName = 'posts';

    protected $schema = array(
        'fields'    => array(
            'id'        => array(
                'map'            => 'id',
                'type'           => 'integer',
                'auto_increment' => true
            ),
            'title'     => array(
                'map'  => 'name',
                'type' => 'string',
            ),
            'creatorId' => array(
                'map'  => 'creator_id',
                'type' => 'int',
            ),
        ),
        'manyToOne' => array(
            'creator' => array(
                'model'      => '\\Bun\\Core\\Model\\User',
                'mappedBy'   => 'creatorId',
                'foreignKey' => 'id',
            )
        ),
        'oneToMany' => array(
            'post2Authors' => array(
                'model'      => '\\Bun\\Core\\Model\\Post2Author',
                'mappedBy'   => 'id',
                'foreignKey' => 'postId'
            )
        )
    );

    /** @var string */
    protected $title;
    /** @var Post2Author[] */
    protected $post2Authors = array();
    /** @var int */
    protected $creatorId;
    /** @var User */
    protected $creator;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Post2Author[]
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
        if(!($post2Authors instanceof ModelArrayProxy)) {
            foreach ($post2Authors as $post2Author) {
                $post2Author->setPost($this);
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
            $post2Author->setPost($this);
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
     * @return User[]
     */
    public function getAuthors()
    {
        $authors = array();
        foreach ($this->getPost2Authors() as $post2Author) {
            $authors[] = $post2Author->getAuthor();
        }

        return $authors;
    }

    /**
     * @return int
     */
    public function getCreatorId()
    {
        return $this->creatorId;
    }

    /**
     * @param int $creatorId
     * @return $this
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;

        return $this;
    }

    /**
     * @param User $creator
     * @return $this
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }
}