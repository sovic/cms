<?php

namespace Sovic\Cms\Tests\Ai;

use PHPUnit\Framework\TestCase;
use Sovic\Cms\Ai\ComponentLibraryLoader;
use Sovic\Cms\Ai\Exception\ComponentLibraryException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class ComponentLibraryLoaderTest extends TestCase
{
    private const Dir = __DIR__ . '/fixtures/components';

    public function testLoadsComponentsSortedByName(): void
    {
        $library = (new ComponentLibraryLoader(self::Dir, self::Dir . '/STYLEGUIDE.md'))->load();

        self::assertSame(['feature-card', 'hero'], array_keys($library->components));
        self::assertStringContainsString('class="hero"', $library->components['hero']);
        self::assertStringStartsWith('# Styleguide', $library->styleguide);
    }

    public function testIgnoresNonHtmlFiles(): void
    {
        $library = (new ComponentLibraryLoader(self::Dir, null))->load();

        self::assertArrayNotHasKey('notes', $library->components);
        self::assertSame('', $library->styleguide);
    }

    public function testUsesCache(): void
    {
        $cache = new ArrayAdapter();
        $loader = new ComponentLibraryLoader(self::Dir, self::Dir . '/STYLEGUIDE.md', $cache);

        self::assertEquals($loader->load(), $loader->load());
        self::assertCount(1, $cache->getValues());
    }

    public function testMissingDirectoryThrows(): void
    {
        $this->expectException(ComponentLibraryException::class);
        (new ComponentLibraryLoader(self::Dir . '/missing', null))->load();
    }

    public function testMissingStyleguideThrows(): void
    {
        $this->expectException(ComponentLibraryException::class);
        (new ComponentLibraryLoader(self::Dir, self::Dir . '/missing.md'))->load();
    }

    public function testTooLargeFileThrows(): void
    {
        $dir = sys_get_temp_dir() . '/sovic-cms-ai-test-' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir . '/big.html', str_repeat('a', ComponentLibraryLoader::MaxFileBytes + 1));

        try {
            $this->expectException(ComponentLibraryException::class);
            (new ComponentLibraryLoader($dir, null))->load();
        } finally {
            unlink($dir . '/big.html');
            rmdir($dir);
        }
    }
}
