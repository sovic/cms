<?php

namespace Sovic\Cms\Gallery;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Entity\GalleryModelInterface;
use Symfony\Contracts\Service\Attribute\Required;

final class GalleryManager
{
    /**
     * @var string[] gallery names allowed besides the default (model-named) gallery
     */
    private array $galleries;

    private EntityManagerInterface $entityManager;

    private FilesystemOperator $filesystemOperator;

    private string $modelName;
    private int $modelId;

    public function __construct(GalleryModelInterface $model)
    {
        $this->modelName = $model->getGalleryModelName();
        $this->modelId = (int) $model->getGalleryModelId();
        $this->galleries = $model->getGalleryNames();
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function setFilesystemOperator(FilesystemOperator $filesystemOperator): void
    {
        $this->filesystemOperator = $filesystemOperator;
    }

    /**
     * Get a gallery, if it doesn't exist, create it.
     */
    public function loadGallery(?string $galleryName = null): Gallery
    {
        $galleryName = $this->validateGalleryName($galleryName);
        $gallery = $this->getGallery($galleryName);

        return $gallery ?? $this->createGallery($galleryName);
    }

    public function createGallery(?string $galleryName = null): Gallery
    {
        $galleryName = $this->validateGalleryName($galleryName);

        $entity = new \Sovic\Cms\Entity\Gallery();
        $entity->setModel($this->modelName);
        $entity->setModelId($this->modelId);
        $entity->setName($galleryName);
        $entity->setCreateDate(new DateTimeImmutable());
        $entity->setIsDownloadEnabled(false);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $gallery = new Gallery($entity);
        $gallery->setEntityManager($this->entityManager);
        if (isset($this->filesystemOperator)) {
            $gallery->setFilesystemOperator($this->filesystemOperator);
        }

        return $gallery;
    }

    public function getGallery(?string $galleryName = null): ?Gallery
    {
        $galleryName = $this->validateGalleryName($galleryName);
        $repo = $this->entityManager->getRepository(\Sovic\Cms\Entity\Gallery::class);
        $entity = $repo->findOneBy(
            [
                'model' => $this->modelName,
                'modelId' => $this->modelId,
                'name' => $galleryName,
            ]
        );
        if ($entity === null) {
            return null;
        }

        $gallery = new Gallery($entity);
        $gallery->setEntityManager($this->entityManager);
        if (isset($this->filesystemOperator)) {
            $gallery->setFilesystemOperator($this->filesystemOperator);
        }

        return $gallery;
    }

    public function getGalleries(): array
    {
        $repo = $this->entityManager->getRepository(\Sovic\Cms\Entity\Gallery::class);
        $entities = $repo->findBy(
            [
                'model' => $this->modelName,
                'modelId' => $this->modelId,
            ]
        );
        $galleries = [];
        foreach ($entities as $entity) {
            $gallery = new Gallery($entity);
            $gallery->setEntityManager($this->entityManager);
            if (isset($this->filesystemOperator)) {
                $gallery->setFilesystemOperator($this->filesystemOperator);
            }
            $galleries[] = $gallery;
        }

        return $galleries;
    }

    private function validateGalleryName(?string $galleryName): string
    {
        if ($galleryName === null || $galleryName === $this->modelName) {
            $galleryName = $this->modelName;
        } else if (!in_array($galleryName, $this->galleries, true)) {
            $errorMessage = 'invalid gallery name [' . implode('|', [$this->modelName, ...$this->galleries]) . ']';
            throw new InvalidArgumentException($errorMessage);
        }

        return $galleryName;
    }
}
