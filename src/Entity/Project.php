<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'project')]
#[ORM\Entity]
class Project
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    protected string $name;

    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    protected string $slug;

    #[ORM\Column(name: 'domains', type: Types::TEXT, length: 1000, nullable: false)]
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
