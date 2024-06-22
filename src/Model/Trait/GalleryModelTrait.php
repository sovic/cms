<?php

namespace Sovic\Cms\Model\Trait;

use Sovic\Gallery\Gallery\Gallery;
use Sovic\Gallery\Gallery\GalleryManager;

trait GalleryModelTrait
{
    protected GalleryManager $galleryManager;

    public function getGalleryManager(): GalleryManager
    {
        if (!isset($this->galleryManager)) {
            $manager = new GalleryManager($this->getGalleryModelName(), $this->getGalleryModelId());
            $manager->setEntityManager($this->entityManager);

            $this->galleryManager = $manager;
        }

        return $this->galleryManager;
    }

    public function getCoverImage(): ?array
    {
        return $this->getGallery()->getCoverImage();
    }

    public function getGallery(): Gallery
    {
        return $this->getGalleryManager()->loadGallery();
    }

    public function getGalleries(): array
    {
        return $this->getGalleryManager()->getGalleries();
    }
}
