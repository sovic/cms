<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'page_tag')]
#[Index(name: 'page_id', columns: ['page_id'])]
#[Index(name: 'tag_id', columns: ['tag_id'])]
#[Entity]
class PageTag
{
    #[Column(name: 'page_id', type: Types::INTEGER)]
    #[Id]
    protected int $pageId;

    #[ManyToOne(targetEntity: Page::class)]
    #[JoinColumn(name: 'page_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Page $page;

    #[Column(name: 'tag_id', type: Types::INTEGER)]
    #[Id]
    protected int $tagId;

    #[ManyToOne(targetEntity: Tag::class)]
    #[JoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Tag $tag;

    public function getPageId(): int
    {
        return $this->pageId;
    }

    public function setPageId(int $pageId): void
    {
        $this->pageId = $pageId;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function setTagId(int $tagId): void
    {
        $this->tagId = $tagId;
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function setPage(Page $page): void
    {
        $this->page = $page;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }
}
