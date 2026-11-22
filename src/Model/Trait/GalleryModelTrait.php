<?php

namespace Sovic\Cms\Model\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Gallery\Gallery;
use Sovic\Cms\Gallery\GalleryManager;

/**
 * Use in a class implementing GalleryModelInterface.
 *
 * @property EntityManagerInterface $entityManager
 */
trait GalleryModelTrait
{
    protected GalleryManager $galleryManager;

    public function getGalleryManager(): GalleryManager
    {
        if (!isset($this->galleryManager)) {
            $manager = new GalleryManager($this);
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
