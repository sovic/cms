<?php

namespace Sovic\Cms\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait IsPublicTrait
{
    #[Column(name: 'is_public', type: Types::BOOLEAN, nullable: false, options: ['default' => 0])]
    protected bool $isPublic = false;

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }
}