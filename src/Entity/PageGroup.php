<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Repository\PageGroupRepository;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;

#[Table(name: 'page_group')]
#[Entity(repositoryClass: PageGroupRepository::class)]
class PageGroup
{
    use IdentityColumnTrait;

    #[Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
