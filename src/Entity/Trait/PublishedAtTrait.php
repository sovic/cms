<?php

namespace Sovic\Cms\Entity\Trait;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait PublishedAtTrait
{
    #[Column(name: 'published_at', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    protected ?DateTimeImmutable $publishedAt = null;

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }
}
