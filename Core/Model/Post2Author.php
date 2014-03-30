<?php
namespace Bun\Core\Model;

/**
 * Class Post2Author
 *
 * @package Bun\Core\Model
 */
class Post2Author extends AbstractModel
{
    protected $tableName = 'post2author';

    protected $schema = array(
        'fields' => array(
            'id' => array(
                'map'            => 'id',
                'type'           => 'integer',
                'auto_increment' => true
            ),
            'postId' => array(
                'map' => 'post_id',
                'type' => 'integer',
            ),
            'authorId' => array(
                'map' => 'author_id',
                'type' => 'integer',
            )
        ),
        'manyToOne' => array(
            'author' => array(
                'model' => '\\Bun\\Core\\Model\\User',
                'mappedBy' => 'authorId',
                'foreignKey' => 'id'
            ),
            'post' => array(
                'model' => '\\Bun\\Core\\Model\\Post',
                'mappedBy' => 'postId',
                'foreignKey' => 'id'
            ),
        )
    );

    /** @var int */
    protected $postId;
    /** @var int */
    protected $authorId;
    /** @var User */
    protected $author;
    /** @var Post */
    protected $post;

    /**
     * @return int
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * @param $postId
     * @return $this
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @param $authorId
     * @return $this
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;

        return $this;
    }

    /**
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param Post $post
     * @return $this
     */
    public function setPost($post)
    {
        $this->post = $post;
        $post->addPost2Author($this);

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param User $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }
}