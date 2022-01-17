<?php

namespace SovicCms\Post;

use SovicCms\ORM\AbstractEntityModel;
use SovicCms\ORM\EntityModelGalleryInterface;

/**
 * @method \SovicCms\Entity\Post getEntity()
 */
class Post extends AbstractEntityModel implements EntityModelGalleryInterface
{
    public function getId(): int
    {
        return $this->getEntity()->getId();
    }

    public function getGalleryModelName(): string
    {
        return 'post';
    }

    public function getGalleryModelId(): string
    {
        return $this->getId();
    }

    public function getTitlePhoto(): ?array
    {
        $galleryManager = $this->getGalleryManager();

        return $galleryManager->getTitlePhoto('post');
    }

    public function getGallery(): array
    {
        $galleryManager = $this->getGalleryManager();

        return $galleryManager->getGallery('post');
    }
}
