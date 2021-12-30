<?php

namespace SovicCms\Gallery;

use SovicCms\Entity\GalleryItem;

class GalleryHelper
{
    public const SIZE_THUMB = 'small';
    public const SIZE_BIG = 'big';
    public const SIZE_FULL = 'full';

    public const SIZES = [
        self::SIZE_THUMB,
        self::SIZE_BIG,
        self::SIZE_FULL,
    ];

    // simplify sizes selection with predefined sets
    public const SIZES_SET_ALL = [
        self::SIZE_THUMB,
        self::SIZE_BIG,
        self::SIZE_FULL,
    ];

    public static function getMediumPaths(GalleryItem $item, array $sizes = [self::SIZE_THUMB, self::SIZE_BIG]): array
    {
        $domain = 'https://www.ods.cz'; // TODO
        $dir = sprintf('%04d', (int) ($item->getId() / 100));
        $basePath = $domain . '/media/content/' . $item->getModel() . '/' . $dir;
        $result = [];
        foreach ($sizes as $size) {
            if (!in_array($size, self::SIZES, true)) {
                continue;
            }
            if ($size === self::SIZE_FULL) {
                $result[$size] = $basePath . '/' . $item->getId() . '.' . $item->getFile();
            } else {
                $result[$size] = $basePath . '/' . $item->getId() . '_' . $size . '.' . $item->getFile();
            }
        }

        return $result;
    }
}
