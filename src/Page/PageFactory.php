<?php

namespace Sovic\Cms\Page;

use Sovic\Cms\ORM\EntityModelFactory;
use Sovic\Cms\Entity\Page as PageEntity;

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
        $model = $this->loadModelBy(PageEntity::class, Page::class, ['urlId' => $urlId]);
        if (null === $model) {
            return null;
        }
        if (!$allowPrivate && !$model->entity->isPublic()) {
            return null;
        }

        return $model;
    }
}
