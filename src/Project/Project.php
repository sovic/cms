<?php

namespace Sovic\Cms\Project;

use Sovic\Cms\ORM\AbstractEntityModel;

/**
 * @method \Sovic\Cms\Entity\Project getEntity()
 */
class Project extends AbstractEntityModel
{
    public function getId(): int
    {
        return $this->getEntity()->getId();
    }
}
