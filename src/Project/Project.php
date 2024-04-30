<?php

namespace Sovic\Cms\Project;

use Sovic\Cms\ORM\AbstractEntityModel;

/**
 * @property \Sovic\Cms\Entity\Project entity
 */
class Project extends AbstractEntityModel
{
    public function getId(): int
    {
        return $this->entity->getId();
    }
}
