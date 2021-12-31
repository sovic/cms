<?php

namespace SovicCms\ORM;

use SovicCms\Gallery\GalleryManager;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

abstract class AbstractEntityModel
{
    protected EntityManagerInterface $entityManager;
    protected GalleryManager $galleryManager;
    protected mixed $entity;

    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function flush(): void
    {
        $this->entityManager->persist($this->entity);
        $this->entityManager->flush();
    }

    public function remove(): void
    {
        $this->entityManager->remove($this->entity);
        $this->entityManager->flush();
    }

    public function refresh(): void
    {
        $this->entityManager->refresh($this->entity);
    }

    public function getGalleryManager(): GalleryManager
    {
        if (!$this instanceof EntityModelGalleryInterface) {
            throw new RuntimeException('Not yet implemented');
        }
        if (null === $this->galleryManager) {
            /** @var EntityModelGalleryInterface $this */
            $modelName = $this->getGalleryModelName();
            $this->galleryManager = new GalleryManager($this->getEntityManager(), $modelName);
        }

        return $this->galleryManager;
    }
}
