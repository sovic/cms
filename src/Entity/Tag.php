<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Entity\Trait\PrivateSlugTrait;
use Sovic\Common\Entity\Project;

#[Table(name: 'tag')]
#[Index(name: 'project_id', columns: ['project_id'])]
#[Entity]
class Tag
{
    use PrivateSlugTrait;

    #[Column(name: 'id', type: Types::INTEGER)]
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ManyToOne(targetEntity: Project::class)]
    #[JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Project $project;

    #[Column(name: 'name', type: Types::STRING, length: 100, nullable: true)]
    protected string $name;

    #[Column(name: 'url_id', type: Types::STRING, length: 100, nullable: true)]
    protected string $urlId;

    #[Column(name: 'is_public', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $isPublic = false;

    #[Column(name: 'lang', type: Types::STRING, length: 5, nullable: true, options: ['default' => 'cs'])]
    protected ?string $lang = 'cs';

    #[Column(name: 'group_id', type: Types::INTEGER, nullable: true)]
    protected int $groupId;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
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

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setUrlId(string $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function isIsPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): void
    {
        $this->lang = $lang;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }
}
