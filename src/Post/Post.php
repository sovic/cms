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
        return $this->getGalleryManager()->getTitlePhoto('post');
    }

    public function getGallery(): array
    {
        return $this->getGalleryManager()->getGallery('post');
    }
}
