<?php

namespace Sovic\Cms\Page;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\GalleryModelInterface;
use Sovic\Cms\Entity\Page as PageEntity;
use Sovic\Cms\Gallery\GalleryModelProviderInterface;

final readonly class PageGalleryModelProvider implements GalleryModelProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PageFactory            $pageFactory,
    ) {
    }

    public function getGalleryModelName(): string
    {
        return 'page';
    }

    public function loadGalleryModel(int $modelId): ?GalleryModelInterface
    {
        $entity = $this->entityManager->find(PageEntity::class, $modelId);

        return $entity ? $this->pageFactory->loadByEntity($entity) : null;
    }
}
