<?php

namespace Sovic\Cms\Gallery;

use Sovic\Cms\Entity\GalleryItem;
use Sovic\Common\Model\EntityModelFactory;

final class GalleryFactory extends EntityModelFactory
{
    public function loadByEntity(?\Sovic\Cms\Entity\Gallery $entity = null): ?Gallery
    {
        return $this->loadEntityModel($entity, \Sovic\Cms\Entity\Gallery::class);
    }

    public function loadById(int $id): ?Gallery
    {
        return $this->loadModelBy(
            \Sovic\Cms\Entity\Gallery::class,
            Gallery::class,
            ['id' => $id]
        );
    }

    public function loadByGalleryItemId(int $galleryItemId): ?Gallery
    {
        $galleryItem = $this->getEntityManager()->getRepository(GalleryItem::class)->find($galleryItemId);
        if (null === $galleryItem) {
            return null;
        }

        return $this->loadById($galleryItem->getGallery()->getId());
    }

    protected function loadEntityModel(mixed $entity, string $modelClass): mixed
    {
        if (null === $entity) {
            return null;
        }

        $model = new $modelClass($entity);
        $model->setEntityManager($this->getEntityManager());

        return $model;
    }
}
