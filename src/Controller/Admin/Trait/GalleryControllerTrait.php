<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Sovic\Cms\Gallery\GalleryManager;

trait GalleryControllerTrait
{
    /**
     * Assigns template variables:
     *  - galleries: array<string, array{is_download_enabled: bool, items: array<int, array<string, mixed>>}>
     *      keyed by gallery name, items are GalleryItemResultSet::toArray() rows enriched with 'is_cover'
     *  - gallery_item_count: int
     */
    protected function assignGalleries(object $model, array $names, ?string $baseUrl = null): void
    {
        $galleries = [];
        $galleryItemCount = 0;
        /** @var GalleryManager $galleryManager */
        $galleryManager = $model->getGalleryManager();

        foreach ($names as $galleryName) {
            $gallery = $galleryManager->getGallery($galleryName);
            if ($gallery === null) {
                $galleries[$galleryName] = [
                    // matches GalleryManager::createGallery() defaults
                    'is_download_enabled' => true,
                    'items' => [],
                ];
                continue;
            }
            $resultSet = $gallery->getItemsResultSet();
            if ($baseUrl) {
                $resultSet->setBaseUrl($baseUrl);
            }

            $items = $resultSet->toArray();
            $cover = $gallery->getCoverImage();
            $coverId = $cover ? $cover['id'] : null;
            foreach ($items as &$item) {
                $item['is_cover'] = ($item['id'] === $coverId);
            }
            unset($item);
            $galleries[$galleryName] = [
                'is_download_enabled' => $gallery->getEntity()->isDownloadEnabled(),
                'items' => $items,
            ];
            $galleryItemCount += count($items);
        }

        $this->assign('galleries', $galleries);
        $this->assign('gallery_item_count', $galleryItemCount);
    }
}
