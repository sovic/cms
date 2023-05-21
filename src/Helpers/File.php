<?php

namespace Sovic\Cms\Helpers;

class File
{
    public static function publicFileName(string $filename, string $extension = null): string
    {
        $filename = str_replace(['.', '_', '-'], ' ', $filename);

        return $extension ? $filename . ' (' . $extension . ')' : $filename;
    }
}
