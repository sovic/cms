<?php

namespace Sovic\Cms\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait MetaColumnsTrait
{
    #[Column(name: 'meta_title', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaTitle = null;

    #[Column(name: 'meta_description', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaDescription = null;

    #[Column(name: 'meta_keywords', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaKeywords = null;

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }
}
