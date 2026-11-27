<?php /** @noinspection HtmlDeprecatedAttribute */

/** @noinspection HtmlUnknownTarget */

namespace Sovic\Cms\Tests\Email;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sovic\Cms\Email\EmailHtmlSanitizer;

final class EmailHtmlSanitizerTest extends TestCase
{
    #[DataProvider('provider')]
    public function testSanitize(string $input, string $expected): void
    {
        self::assertSame($expected, (new EmailHtmlSanitizer())->sanitize($input));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function provider(): array
    {
        return [
            'empty' => [
                '   ',
                '',
            ],
            'keeps allowed elements' => [
                '<h1>Nadpis</h1><h6>Malý</h6><p>Text <i>kurzíva</i><br>řádek</p><ul><li>a</li></ul><ol><li>b</li></ol>',
                '<h1>Nadpis</h1><h6>Malý</h6><p>Text <i>kurzíva</i><br>řádek</p><ul><li>a</li></ul><ol><li>b</li></ol>',
            ],
            'keeps czech diacritics and nbsp' => [
                '<p>Příliš žluťoučký&nbsp;kůň</p>',
                '<p>Příliš žluťoučký&nbsp;kůň</p>',
            ],
            'unwraps disallowed elements' => [
                '<div><p><strong>Tučně</strong> <span style="color:red">červeně</span></p></div><table><tr><td>buňka</td></tr></table>',
                '<p><strong>Tučně</strong> červeně</p>buňka',
            ],
            'renames em to i' => [
                '<p><em>kurzíva</em></p>',
                '<p><i>kurzíva</i></p>',
            ],
            'renames b to strong' => [
                '<p><b class="x">tučně</b></p>',
                '<p><strong>tučně</strong></p>',
            ],
            'removes executable elements with content' => [
                '<p>A</p><script>alert(1)</script><style>p{}</style><iframe src="https://x"></iframe><svg><a href="#">x</a></svg>',
                '<p>A</p>',
            ],
            'removes comments' => [
                '<p>A<!-- comment --></p>',
                '<p>A</p>',
            ],
            'removes style, class and event attributes' => [
                '<p style="text-align: center" class="lead" onclick="alert(1)">A</p><h2 style="color:red">B</h2>',
                '<p>A</p><h2>B</h2>',
            ],
            'keeps cta link' => [
                '<p><a class="int_cta_link" href="https://example.com/a?b=1&amp;c=2" target="_blank" rel="noopener">Klikněte</a></p>',
                '<p><a class="int_cta_link" href="https://example.com/a?b=1&amp;c=2" target="_blank" rel="noopener">Klikněte</a></p>',
            ],
            'filters link classes and target' => [
                '<a class="foo ext_link" href="/a" target="_top" onmouseover="x()">A</a>',
                '<a class="ext_link" href="/a">A</a>',
            ],
            'keeps placeholders' => [
                '<p><a class="int_cta_link" href="{link}">Odkaz</a> {name}</p><img src="{image_url}" alt="">',
                '<p><a class="int_cta_link" href="{link}">Odkaz</a> {name}</p><img src="{image_url}" alt="">',
            ],
            'keeps mailto and tel' => [
                '<a href="mailto:info@example.com">e</a><a href="tel:+420123">t</a>',
                '<a href="mailto:info@example.com">e</a><a href="tel:+420123">t</a>',
            ],
            'removes unsafe urls' => [
                '<a href="javascript:alert(1)">A</a><a href=" jav&#x09;ascript:alert(1)">B</a><a href="data:text/html,x">C</a>',
                '<a>A</a><a>B</a><a>C</a>',
            ],
            'keeps image attributes' => [
                '<img src="https://example.com/a.jpg" alt="a" width="200" height="100px" style="float:left" onerror="x()">',
                '<img src="https://example.com/a.jpg" alt="a" width="200">',
            ],
            'removes image without safe src' => [
                '<p>A<img src="data:image/svg+xml;base64,AAA" alt="a"><img alt="b"></p>',
                '<p>A</p>',
            ],
        ];
    }
}
