<?php

namespace Sovic\Cms\Project;

use Sovic\Cms\ORM\EntityModelFactory;
use Sovic\Cms\Entity\Project as ProjectEntity;

final class ProjectFactory extends EntityModelFactory
{
    public function loadById(int $id): ?Project
    {
        return $this->loadModelBy(ProjectEntity::class, Project::class, ['id' => $id]);
    }
}
