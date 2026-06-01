<?php

namespace Sovic\Cms\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Sovic\Cms\Entity\MenuItem;

trait MenuItemTrait
{
    #[Column(name: 'menu_item_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    private ?int $menuItemId = null;

    #[ManyToOne(targetEntity: MenuItem::class)]
    #[JoinColumn(name: 'menu_item_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MenuItem $menuItem = null;

    public function getMenuItemId(): ?int
    {
        return $this->menuItemId;
    }

    public function getMenuItem(): ?MenuItem
    {
        return $this->menuItem;
    }

    public function setMenuItem(?MenuItem $menuItem): void
    {
        $this->menuItem = $menuItem;
        $this->menuItemId = $menuItem?->getId();
    }
}
