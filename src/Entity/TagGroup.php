<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Repository\TagGroupRepository;
use Sovic\Common\Entity\Project;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;

/**
 * Logical group of tags (e.g. "Regions", "Topics"), used for orientation in admin
 * and for grouped tag facets / filters on the frontend.
 */
#[Table(name: 'tag_group')]
#[Index(name: 'project_id', columns: ['project_id'])]
#[Entity(repositoryClass: TagGroupRepository::class)]
class TagGroup
{
    use IdentityColumnTrait;

    #[ManyToOne(targetEntity: Project::class)]
    #[JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE', options: ['default' => null])]
    protected ?Project $project = null;

    #[Column(name: 'name', type: Types::STRING, length: 100, nullable: false)]
    protected string $name = '';

    #[Column(name: 'url_id', type: Types::STRING, length: 100, nullable: true, options: ['default' => null])]
    protected ?string $urlId = null;

    #[Column(name: 'position', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    protected int $position = 0;

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUrlId(): ?string
    {
        return $this->urlId;
    }

    public function setUrlId(?string $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
