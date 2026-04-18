<?php

namespace Sovic\Cms\Gallery;

use Sovic\Cms\Entity\GalleryItem;

class GalleryItemResultSet
{
    private string $baseUrl = '';

    /**
     * @param GalleryItem[] $galleryItems
     */
    public function __construct(private readonly array $galleryItems)
    {
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function toArray(): array
    {
        $results = [];
        foreach ($this->galleryItems as $item) {
            if (null === $item) {
                continue;
            }
            $result = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'extension' => $item->getExtension(),
                'filesize' => $item->getFilesize(),
                'url' => $this->baseUrl . '/dl/' . $item->getId(), // TODO add a config with route
                'model' => $item->getModel(),
                'model_id' => $item->getModelId(),
                'is_hero' => $item->isHero(),
                'is_hero_mobile' => $item->isHeroMobile(), // TODO merge with is_hero and use variants / selected area
                'width' => $item->getWidth(),
                'height' => $item->getHeight(),
            ];
            $mediumPaths = GalleryHelper::getMediaPaths($item, $this->baseUrl, GalleryHelper::SIZES_SET_ALL);
            $results[] = array_merge($result, $mediumPaths);
        }

        return $results;
    }
}
