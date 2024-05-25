<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'project')]
#[Entity]
class Project
{
    #[Column(name: 'id', type: Types::INTEGER)]
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    protected string $name;

    #[Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    protected string $slug;

    #[Column(name: 'domains', type: Types::TEXT, length: 1000, nullable: false)]
    protected string $domains;

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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDomains(): string
    {
        return $this->domains;
    }

    public function setDomains(string $domains): void
    {
        $this->domains = $domains;
    }
}
