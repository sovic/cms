<?php

namespace Sovic\Cms\Page;

use Sovic\Cms\ORM\EntityModelFactory;
use Sovic\Cms\Entity\Page as PageEntity;
use Sovic\Cms\Project\ProjectEntityModelFactoryInterface;
use Sovic\Cms\Project\ProjectEntityModelFactoryTrait;

final class PageFactory extends EntityModelFactory implements ProjectEntityModelFactoryInterface
{
    use ProjectEntityModelFactoryTrait;

    public function loadByEntity(?PageEntity $entity = null): ?Page
    {
        return $this->loadEntityModel($entity, Page::class);
    }

    public function loadById(int $id): ?Page
    {
        $criteria = $this->getProjectSelectCriteria();
        $criteria['id'] = $id;

        return $this->loadModelBy(PageEntity::class, Page::class, $criteria);
    }

    public function loadByUrlId(string $urlId, bool $allowPrivate = false): ?Page
    {
        $criteria = $this->getProjectSelectCriteria();
        $urlId = trim($urlId, '/\\'); // trim leading / trailing slashes
        $criteria['urlId'] = $urlId;

        $model = $this->loadModelBy(PageEntity::class, Page::class, $criteria);
        if (null === $model) {
            return null;
        }
        if (!$allowPrivate && !$model->entity->isPublic()) {
            return null;
        }

        return $model;
    }
}
