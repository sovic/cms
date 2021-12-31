<?php

namespace SovicCms\Page;

use SovicCms\ORM\AbstractEntityModel;
use SovicCms\ORM\EntityModelGalleryInterface;

/**
 * @method \SovicCms\Entity\Page getEntity()
 */
class Page extends AbstractEntityModel implements EntityModelGalleryInterface
{
    public function getId(): int
    {
        return $this->getEntity()->getId();
    }

    public function getGalleryModelName(): string
    {
        return 'page';
    }

    public function getHeading(): array
    {
        return explode('/', $this->getEntity()->getHeading());
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $heading = array_map('trim', explode('/', $this->getEntity()->getHeading()));
        $urlParts = array_map('trim', explode('/', $this->getEntity()->getUrlId()));

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

        /*
         * TODO
        if (!empty($this->variables['breadcrumbs_after'])) {
            foreach ($this->variables['breadcrumbs_after'] as $breadcrumb) {
                $breadcrumbs[] = $breadcrumb;
            }
        }
        */

        return $breadcrumbs;
    }

    /**
     * Double check what you add to this method, this will be loaded on every page!
     *
     * @return array
     */
    public function toArray(): array
    {
        if (null === $this->getEntity()) {
            return [];
        }

        $id = $this->getId();
        $entity = $this->getEntity();
        $galleryManager = $this->getGalleryManager();
        $heroImages = $galleryManager->getHeroImages($id, 'page');

        return [
            'header' => $entity->getHeader(),
            'hero_image' => $heroImages['hero'],
            'hero_image_mobile' => $heroImages['hero_mobile'],
            'gallery' => $galleryManager->getGallery($id, 'gallery'),
            'menu_active' => '/' . $entity->getUrlId(),
        ];
    }
}
