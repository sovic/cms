<?php

namespace Sovic\Cms\Tests\Ai;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sovic\Cms\Ai\HtmlSanitizer;

final class HtmlSanitizerTest extends TestCase
{
    #[DataProvider('provider')]
    public function testSanitize(string $input, string $expected): void
    {
        self::assertSame($expected, (new HtmlSanitizer())->sanitize($input));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provider(): array
    {
        return [
            'keeps components and classes' => [
                '<section class="hero"><div class="hero__inner"><h2 class="hero__title">Nadpis</h2></div></section>',
                '<section class="hero"><div class="hero__inner"><h2 class="hero__title">Nadpis</h2></div></section>',
            ],
            'keeps czech diacritics' => [
                '<p>Příliš žluťoučký kůň úpěl ďábelské ódy</p>',
                '<p>Příliš žluťoučký kůň úpěl ďábelské ódy</p>',
            ],
            'keeps empty icon' => [
                '<a class="btn" href="/kontakt"><i class="bi bi-phone"></i> Kontakt</a>',
                '<a class="btn" href="/kontakt"><i class="bi bi-phone"></i> Kontakt</a>',
            ],
            'removes script and style' => [
                '<p>A</p><script>alert(1)</script><style>p{}</style>',
                '<p>A</p>',
            ],
            'removes event handlers and style attr' => [
                '<img src="/a.jpg" alt="a" onerror="alert(1)" style="width:1px">',
                '<img src="/a.jpg" alt="a">',
            ],
            'removes javascript url' => [
                '<a href=" javascript:alert(1)">x</a>',
                '<a>x</a>',
            ],
            'removes data url on link' => [
                '<a href="data:text/html,x">x</a>',
                '<a>x</a>',
            ],
            'keeps data image' => [
                '<img src="data:image/png;base64,AAAA">',
                '<img src="data:image/png;base64,AAAA">',
            ],
            'removes non https iframe' => [
                '<p>A</p><iframe src="http://example.com"></iframe>',
                '<p>A</p>',
            ],
            'keeps https iframe' => [
                '<iframe src="https://www.youtube.com/embed/x" allowfullscreen></iframe>',
                '<iframe src="https://www.youtube.com/embed/x" allowfullscreen></iframe>',
            ],
            'removes document wrappers' => [
                '<html><head><title>x</title></head><body><p>A</p></body></html>',
                '<p>A</p>',
            ],
            'removes comments and forms' => [
                '<!-- c --><form action="/x"><input name="a"></form><p>A</p>',
                '<p>A</p>',
            ],
            'empty' => [
                '   ',
                '',
            ],
        ];
    }
}
