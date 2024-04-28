<?php

namespace Sovic\Cms\Page;

use Sovic\Cms\ORM\EntityModelFactory;
use Sovic\Cms\Entity\Page as PageEntity;
use Sovic\Cms\Project\Project;

final class ProjectPageFactory extends EntityModelFactory
{
    public function loadByEntity(Project $project, PageEntity $entity): ?Page
    {
        if ($project->getId() !== $entity->getProject()->getId()) {
            return null;
        }

        return $this->loadEntityModel($entity, Page::class);
    }

    public function loadById(Project $project, int $id): ?Page
    {
        return $this->loadModelBy(
            PageEntity::class,
            Page::class,
            [
                'id' => $id,
                'project' => $project->getEntity(),
            ]
        );
    }

    public function loadByUrlId(Project $project, string $urlId, bool $allowPrivate = false): ?Page
    {
        $urlId = trim($urlId, '/\\'); // trim leading / trailing slashes
        $model = $this->loadModelBy(
            PageEntity::class,
            Page::class,
            [
                'urlId' => $urlId,
                'project' => $project->getEntity(),
            ]
        );
        if (null === $model) {
            return null;
        }
        if (!$allowPrivate && !$model->getEntity()->isPublic()) {
            return null;
        }

        return $model;
    }
}
