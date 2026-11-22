<?php

namespace Sovic\Cms\Tests\Tag;

use PHPUnit\Framework\TestCase;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Tag\TagColor;

final class TagColorTest extends TestCase
{
    // card, badge-light-primary and badge-light backgrounds, light and dark theme
    private const Backgrounds = ['#ffffff', '#e9f3ff', '#f9f9f9', '#15171c', '#172331', '#1b1c22'];

    public function testPaletteIsVisibleOnAdminBackgrounds(): void
    {
        self::assertCount(10, TagColor::cases());

        foreach (TagColor::cases() as $color) {
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $color->value);
            self::assertNotSame('', $color->trans());

            foreach (self::Backgrounds as $background) {
                $luminance = [$this->getLuminance($color->value), $this->getLuminance($background)];
                $contrast = (max($luminance) + 0.05) / (min($luminance) + 0.05);

                self::assertGreaterThanOrEqual(3, $contrast, $color->name . ' on ' . $background);
            }
        }
    }

    public function testTagColor(): void
    {
        $tag = new Tag();
        self::assertNull($tag->getColor());

        $tag->setColor(TagColor::Teal);
        self::assertSame(TagColor::Teal, $tag->getColor());

        $tag->setColor(null);
        self::assertNull($tag->getColor());
    }

    private function getLuminance(string $hex): float
    {
        $channels = array_map(
            static function (string $part): float {
                $value = hexdec($part) / 255;

                return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
            },
            str_split(substr($hex, 1), 2),
        );

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
