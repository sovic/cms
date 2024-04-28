<?php

namespace Sovic\Cms\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'project')]
#[ORM\Entity]
class Project
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    protected string $name;

    #[ORM\Column(name: 'slug', type: 'string', length: 255, nullable: false)]
    protected string $slug;

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
}
