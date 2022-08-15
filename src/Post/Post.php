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

    public function getHeading(): string
    {
        return $this->getEntity()->getHeading() ?: $this->getEntity()->getName();
    }

    public function getPerex(): ?string
    {
        $perex = $this->getEntity()->getPerex();
        if ($perex) {
            return $perex;
        }
        $content = strip_tags($this->getEntity()->getContent());

        return $content ? substr($content, 0, 250) . '…' : null;
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
