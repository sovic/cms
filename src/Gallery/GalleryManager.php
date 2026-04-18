<?php

namespace Sovic\Cms\Gallery;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use Symfony\Contracts\Service\Attribute\Required;

final class GalleryManager
{
    private array $galleries = [
        'documents',
        'downloads',
    ];

    private EntityManagerInterface $entityManager;

    private FilesystemOperator $filesystemOperator;

    private string $modelName;
    private int $modelId;

    public function __construct(string $modelName, int $modelId)
    {
        $this->modelName = $modelName;
        $this->modelId = $modelId;
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
        $entity->setIsDownloadEnabled(true);

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
            $errorMessage = 'invalid gallery name [' . implode('|', $this->galleries) . ']';
            throw new InvalidArgumentException($errorMessage);
        }

        return $galleryName;
    }
}
