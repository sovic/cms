<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'post_author')]
#[Entity]
class PostAuthor
{
    #[Column(name: 'post_id', type: Types::INTEGER, nullable: false)]
    #[Id]
    protected int $postId;

    #[Column(name: 'author_id', type: Types::INTEGER, nullable: false)]
    #[Id]
    protected int $authorId;

    #[JoinColumn(name: 'post_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ManyToOne(targetEntity: Post::class)]
    protected Post $post;

    #[JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ManyToOne(targetEntity: Author::class)]
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

    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): void
    {
        $this->author = $author;
    }
}
