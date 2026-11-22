<?php /** @noinspection SpellCheckingInspection */

namespace Sovic\Cms\Tag;

/**
 * Tag color label palette, the hex code is stored in DB.
 *
 * Every color has at least 3:1 contrast on light and dark admin backgrounds, see TagColorTest.
 * Changing a code needs a data migration, unknown codes are read as no color.
 */
enum TagColor: string
{
    case Red = '#e03131';
    case Orange = '#e8590c';
    case Gold = '#a88400';
    case Lime = '#5c940d';
    case Green = '#0f9960';
    case Teal = '#0c8599';
    case Blue = '#1c7ed6';
    case Indigo = '#5f6df0';
    case Purple = '#9c4fd6';
    case Pink = '#d6336c';

    public function trans(): string
    {
        return match ($this) {
            self::Red => 'Červená',
            self::Orange => 'Oranžová',
            self::Gold => 'Zlatá',
            self::Lime => 'Limetková',
            self::Green => 'Zelená',
            self::Teal => 'Tyrkysová',
            self::Blue => 'Modrá',
            self::Indigo => 'Indigová',
            self::Purple => 'Fialová',
            self::Pink => 'Růžová',
        };
    }
}
