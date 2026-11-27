<?php

namespace Sovic\Cms\Tests\Email;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sovic\Cms\Email\EmailTheme;
use Sovic\Common\Project\Settings;

final class EmailThemeTest extends TestCase
{
    #[DataProvider('ctaProvider')]
    public function testCtaButton(string $input): void
    {
        $settings = $this->createStub(Settings::class);
        $settings->method('get')->willReturn(null);
        $html = (new EmailTheme($settings))->getFormattedHtml($input);

        self::assertStringContainsString('class="btn btn-primary"', $html);
        self::assertStringContainsString('Background: #0078A2', $html);
        self::assertStringContainsString(' href="https://example.com/a">Klikněte</a>', $html);
        self::assertStringNotContainsString('int_cta_link', $html);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function ctaProvider(): array
    {
        return [
            'class first' => ['<p><a class="int_cta_link" href="https://example.com/a">Klikněte</a></p>'],
            'class last' => ['<p><a href="https://example.com/a" class="int_cta_link">Klikněte</a></p>'],
            'class with style' => ['<p><a href="https://example.com/a" style="color: red" class="int_cta_link">Klikněte</a></p>'],
        ];
    }

    public function testPlainLinkIsNotButton(): void
    {
        $settings = $this->createStub(Settings::class);
        $settings->method('get')->willReturn(null);
        $html = (new EmailTheme($settings))->getFormattedHtml('<p><a class="ext_link" href="/a">A</a></p>');

        self::assertStringNotContainsString('btn-primary', $html);
        self::assertStringContainsString('Text-decoration: underline;', $html);
    }
}
