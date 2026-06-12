<?php

namespace Sovic\Cms\Tests\Tag;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sovic\Cms\Tag\TagName;

final class TagNameTest extends TestCase
{
    #[DataProvider('normalizeProvider')]
    public function testNormalize(string $input, string $expected): void
    {
        self::assertSame($expected, TagName::normalize($input));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function normalizeProvider(): array
    {
        return [
            'strips leading hash' => ['#novinky', 'novinky'],
            'strips multiple hashes' => ['##foo#bar', 'foobar'],
            'trims surrounding whitespace' => ['  sport  ', 'sport'],
            'collapses inner whitespace' => ["foo\t  bar", 'foo bar'],
            'removes commas' => ['foo,bar', 'foobar'],
            'removes punctuation but keeps words' => ['hello! world?', 'hello world'],
            'keeps numbers' => ['rok 2026', 'rok 2026'],
            'keeps unicode letters' => ['příroda', 'příroda'],
            'keeps dots' => ['node.js', 'node.js'],
            'keeps dashes' => ['e-shop', 'e-shop'],
            'keeps dots and dashes together' => ['v1.2-beta', 'v1.2-beta'],
            'only special chars becomes empty' => ['#@!,', ''],
            'empty stays empty' => ['', ''],
        ];
    }

    #[DataProvider('validityProvider')]
    public function testIsValid(string $input, bool $expected): void
    {
        self::assertSame($expected, TagName::isValid($input));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function validityProvider(): array
    {
        return [
            'plain word is valid' => ['novinky', true],
            'word with hash is valid' => ['#sport', true],
            'word with number is valid' => ['rok2026', true],
            'hash only is invalid' => ['###', false],
            'punctuation only is invalid' => ['@!?,', false],
            'empty is invalid' => ['', false],
            'whitespace only is invalid' => ['   ', false],
        ];
    }
}
