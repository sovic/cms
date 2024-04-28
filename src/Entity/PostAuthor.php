<?php

namespace Sovic\Cms\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'post_author')]
#[ORM\Entity]
class PostAuthor
{
    #[ORM\Column(name: 'post_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    protected int $postId;

    #[ORM\Column(name: 'author_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    protected int $authorId;

    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Post::class)]
    protected Post $post;

    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Author::class)]
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
