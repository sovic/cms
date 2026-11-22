<?php

namespace Sovic\Cms\Post;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\GalleryModelInterface;
use Sovic\Cms\Entity\Post as PostEntity;
use Sovic\Cms\Gallery\GalleryModelProviderInterface;

final readonly class PostGalleryModelProvider implements GalleryModelProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PostFactory            $postFactory,
    ) {
    }

    public function getGalleryModelName(): string
    {
        return 'post';
    }

    public function loadGalleryModel(int $modelId): ?GalleryModelInterface
    {
        $entity = $this->entityManager->find(PostEntity::class, $modelId);

        return $entity ? $this->postFactory->loadByEntity($entity) : null;
    }
}
