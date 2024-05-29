<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'post_tag')]
#[Entity]
class PostTag
{
    #[Column(name: 'posts_id', type: Types::INTEGER)]
    #[Id]
    protected int $postId;

    #[ManyToOne(targetEntity: Post::class)]
    #[JoinColumn(name: 'posts_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Post $post;

    #[Column(name: 'tags_id', type: Types::INTEGER)]
    #[Id]
    protected int $tagId;

    #[ManyToOne(targetEntity: Tag::class)]
    #[JoinColumn(name: 'tags_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Tag $tag;

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function setTagId(int $tagId): void
    {
        $this->tagId = $tagId;
    }
}
