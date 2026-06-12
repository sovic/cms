<?php

namespace Sovic\Cms\Tag;

/**
 * Normalization and validation for tag names.
 *
 * A tag name may only contain letters, numbers and single spaces between words.
 * Hashes and any other punctuation are stripped out.
 */
final class TagName
{
    /**
     * Strip hashes and any character that is not a letter, number, whitespace,
     * dot or dash, then collapse whitespace runs into single spaces and trim.
     */
    public static function normalize(string $name): string
    {
        // remove hashes anywhere in the string
        $name = str_replace('#', '', $name);
        // keep only Unicode letters, numbers, whitespace, dots and dashes
        $name = preg_replace('/[^\p{L}\p{N}\s.-]+/u', '', $name) ?? '';
        // collapse any whitespace run into a single space
        $name = preg_replace('/\s+/u', ' ', $name) ?? '';

        return trim($name);
    }

    /**
     * A tag name is valid when it still has content after normalization.
     */
    public static function isValid(string $name): bool
    {
        return self::normalize($name) !== '';
    }
}
