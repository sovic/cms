<?php

namespace Sovic\Cms\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait PrivateSlugTrait
{
    #[Column(
        name: 'private_slug',
        type: Types::STRING,
        length: 32,
        nullable: true,
        options: ['charset' => 'ascii', 'collation' => 'ascii_bin', 'default' => null]
    )]
    private ?string $privateSlug = null;

    public function getPrivateSlug(): ?string
    {
        return $this->privateSlug;
    }

    public function setPrivateSlug(?string $privateSlug): void
    {
        $this->privateSlug = $privateSlug;
    }
}