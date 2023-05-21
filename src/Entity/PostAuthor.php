<?php

namespace Sovic\Cms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sovic\Cms\Entity\PostAuthor
 *
 * @ORM\Table(name="post_author")
 * @ORM\Entity
 */
class PostAuthor
{
    /**
     * @ORM\Column(name="post_id", type="integer", nullable=false)
     * @ORM\Id
     */
    protected int $postId;

    /**
     * @ORM\Column(name="author_id", type="integer", nullable=false)
     * @ORM\Id
     */
    protected int $authorId;

    /**
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected Post $post;

    /**
     * @ORM\ManyToOne(targetEntity="Author")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected Author $author;

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): void
    {
        $this->authorId = $authorId;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post): void
    {
        $this->post = $post;
    }


}
