<?php

namespace SovicCms\Page;

use SovicCms\ORM\EntityModelFactory;
use SovicCms\Entity\Page as PageEntity;

final class PageFactory extends EntityModelFactory
{
    public function loadByEntity(?PageEntity $entity = null): ?Page
    {
        return $this->loadEntityModel($entity, Page::class);
    }

    public function loadById(int $id): ?Page
    {
        return $this->loadModelBy(PageEntity::class, Page::class, ['id' => $id]);
    }

    public function loadByUrlId(string $urlId, bool $allowPrivate = false): ?Page
    {
        $urlId = trim($urlId, '/\\'); // trim leading / trailing slashes
        $model = $this->loadModelBy(PageEntity::class, Page::class, ['rawID' => $urlId]);
        if (null === $model) {
            return null;
        }
        if (!$allowPrivate && !$model->getEntity()->isPublic()) {
            return null;
        }

        return $model;
    }
}
