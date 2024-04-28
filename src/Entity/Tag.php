<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tag')]
#[ORM\Entity]
class Tag
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 100, nullable: true)]
    protected string $name;

    #[ORM\Column(name: 'url_id', type: Types::STRING, length: 100, nullable: true)]
    protected string $urlId;

    #[ORM\Column(name: 'public', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $public = false;

    #[ORM\Column(name: 'lang', type: Types::STRING, length: 5, nullable: true, options: ['default' => 'cs'])]
    protected ?string $lang = 'cs';

    #[ORM\Column(name: 'group_id', type: Types::INTEGER, nullable: true)]
    protected int $groupId;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
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
