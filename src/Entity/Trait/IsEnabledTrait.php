<?php

namespace Sovic\Cms\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait IsEnabledTrait
{
    #[Column(name: 'is_enabled', type: Types::BOOLEAN, nullable: false, options: ['default' => 1])]
    protected bool $isEnabled = true;

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }
}
