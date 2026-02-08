<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Repository\MenuItemRepository;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;

#[Table(name: 'menu_item')]
#[Index(name: 'page_id', columns: ['page_id'])]
#[Index(name: 'parent_id', columns: ['parent_id'])]
#[Entity(repositoryClass: MenuItemRepository::class)]
class MenuItem
{
    use IdentityColumnTrait;

    #[Column(name: 'name', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $name = null;

    #[Column(name: 'url', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $url = null;

    #[Column(name: 'page_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $pageId = null;

    #[Column(name: 'sequence', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $sequence = null;

    #[JoinColumn(name: 'page_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ManyToOne(targetEntity: Page::class)]
    private ?Page $page = null;

    #[Column(name: 'classes', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $classes = null;

    #[Column(name: 'parent_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $parentId = null;

    #[Column(name: 'is_published', type: Types::BOOLEAN, nullable: false, options: ['default' => 0])]
    protected bool $isPublished = false;

    #[Column(name: 'position', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $position = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getUrl(): ?string
    {
        if ($this->url !== null) {
            return '/' . ltrim($this->url, '/');
        }

        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        if ($url !== null) {
            $url = trim($url);
            $url = ltrim($url, '/');
            if ($url === '') {
                $url = null;
            }
        }

        $this->url = $url;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(?int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getPageId(): ?int
    {
        return $this->pageId;
    }

    public function setPageId(?int $pageId): void
    {
        $this->pageId = $pageId;
    }

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function setClasses(?string $classes): void
    {
        $this->classes = $classes;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): void
    {
        $this->isPublished = $isPublished;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): void
    {
        $this->page = $page;
    }

    public function getLink(): ?string
    {
        $link = $this->getUrl();
        if ($this->getPageId() && $this->getPage()) {
            $link = '/' . $this->getPage()->getUrlId();
        }

        return $link;
    }
}
