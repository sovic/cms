<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'post_tag')]
#[ORM\Entity]
class TagPost
{
    #[ORM\Column(name: 'posts_id', type: Types::INTEGER)]
    #[ORM\Id]
    protected int $postId;

    #[ORM\Column(name: 'tags_id', type: Types::INTEGER)]
    #[ORM\Id]
    protected int $tagId;

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
