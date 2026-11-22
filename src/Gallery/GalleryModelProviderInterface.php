<?php

namespace Sovic\Cms\Gallery;

use Sovic\Cms\Entity\GalleryModelInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Loads a gallery model by its gallery model name and id, used where only the name and id are known
 * (admin gallery API). Implement one provider per model, it is registered automatically (autoconfigure).
 */
#[AutoconfigureTag(self::Tag)]
interface GalleryModelProviderInterface
{
    public const Tag = 'sovic_cms.gallery_model_provider';

    /**
     * Gallery model name this provider loads, same as GalleryModelInterface::getGalleryModelName().
     */
    public function getGalleryModelName(): string;

    public function loadGalleryModel(int $modelId): ?GalleryModelInterface;
}
