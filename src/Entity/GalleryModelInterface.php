<?php

namespace Sovic\Cms\Entity;

interface GalleryModelInterface
{
    public function getGalleryModelName(): string;

    public function getGalleryModelId(): string;

    /**
     * Gallery names allowed for the model, besides the default gallery named after the model
     * (getGalleryModelName()).
     *
     * @return string[]
     */
    public function getGalleryNames(): array;
}
