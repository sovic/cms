<?php

namespace Sovic\Cms\Controller\Trait;

use Sovic\Cms\Page\Page;

trait PageControllerTrait
{
    protected function assignPageData(Page $page): void
    {
        $this->assign('page', $page);
        $this->assignArray($page->toArray());
    }

    protected function assignGalleryData(
        Page    $page,
        string  $galleryName = 'page',
        ?string $galleryBaseUrl = null
    ): void {
        $manager = $page->getGalleryManager();
        $gallery = $manager->loadGallery($galleryName);

        $resultSet = $gallery->getItemsResultSet();
        if ($galleryBaseUrl) {
            $resultSet->setBaseUrl($galleryBaseUrl);
        }

        $this->assign('gallery_' . $galleryName, $resultSet->toArray());
    }
}
