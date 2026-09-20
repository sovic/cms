<?php

namespace Sovic\Cms\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sovic\Cms\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function process(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }

    public function testAiDefaults(): void
    {
        $ai = $this->process([])['ai'];

        self::assertNull($ai['components_dir']);
        self::assertSame('claude-opus-5', $ai['model']);
        self::assertSame('medium', $ai['effort']);
        self::assertSame(16000, $ai['max_tokens']);
        self::assertSame(20, $ai['history_limit']);
        self::assertSame([], $ai['preview_css']);
        self::assertSame('', $ai['preview_body_class']);
        self::assertSame('', $ai['preview_wrapper_class']);
    }

    public function testPreviewCssAcceptsSingleString(): void
    {
        $ai = $this->process(['ai' => ['preview_css' => '/build/app.css']])['ai'];

        self::assertSame(['/build/app.css'], $ai['preview_css']);
    }

    public function testPreviewCssAcceptsList(): void
    {
        $ai = $this->process(['ai' => ['preview_css' => ['/build/app.css', 'https://cdn.example.com/fonts.css']]])['ai'];

        self::assertSame(['/build/app.css', 'https://cdn.example.com/fonts.css'], $ai['preview_css']);
    }

    public function testInvalidEffortIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process(['ai' => ['effort' => 'turbo']]);
    }
}
