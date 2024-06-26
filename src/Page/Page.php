<?php

namespace Sovic\Cms\Page;

use Sovic\Common\Model\AbstractEntityModel;
use Sovic\Gallery\Entity\GalleryModelInterface;
use Sovic\Gallery\Model\Trait\GalleryModelTrait;

/**
 * @property \Sovic\Cms\Entity\Page $entity
 */
class Page extends AbstractEntityModel implements GalleryModelInterface
{
    use GalleryModelTrait;

    public function getId(): int
    {
        return $this->entity->getId();
    }

    public function getGalleryModelName(): string
    {
        return 'page';
    }

    public function getGalleryModelId(): string
    {
        return $this->getId();
    }

    public function getHeading(): array
    {
        return explode('/', $this->entity->getHeading());
    }

    public function getMetaTitle(): string
    {
        if ($this->entity->getMetaTitle()) {
            return $this->entity->getMetaTitle();
        }

        return $this->entity->getHeading();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $heading = array_map('trim', explode('/', $this->entity->getHeading()));
        $urlParts = array_map('trim', explode('/', $this->entity->getUrlId()));

        // auto-create breadcrumbs by heading parts (max. 2-parts)
        $i = 0;
        $breadcrumbUrl = '';
        foreach ($heading as $breadcrumb) {
            if (isset($urlParts[$i]) && $i < (count($heading) - 1)) {
                $breadcrumbUrl .= '/' . $urlParts[$i];
                $breadcrumbs[] = ['name' => $breadcrumb, 'url' => $breadcrumbUrl];
            } else {
                $breadcrumbs[] = ['name' => $breadcrumb, 'url' => null];
            }
            $i++;
            if ($i > 1) {
                break;
            }
        }

        return $breadcrumbs;
    }

    /**
     * Double check what you add to this method, this will be loaded on every page!
     */
    public function toArray(): array
    {
        if (null === $this->entity) {
            return [];
        }

        $entity = $this->entity;
        $galleryManager = $this->getGalleryManager();
        $gallery = $galleryManager->loadGallery('page');
        $heroImage = $gallery->getHeroImage();

        return [
            // meta
            'meta_title' => $this->getMetaTitle(),
            'meta_description' => $entity->getMetaDescription(),
            'meta_keywords' => $entity->getMetaKeywords(),

            //
            'gallery' => $gallery,
            'header' => $entity->getHeader(),
            'hero_image' => $heroImage,
            'menu_active' => '/' . $entity->getUrlId(),
            'show_toc', $entity->hasToc(),
        ];
    }
}
