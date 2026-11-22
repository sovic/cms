<?php

namespace Sovic\Cms\Gallery;

final class GalleryUtils
{
    public const ImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public static function isImageExtension(?string $extension): bool
    {
        return $extension !== null && in_array(strtolower($extension), self::ImageExtensions, true);
    }
}
