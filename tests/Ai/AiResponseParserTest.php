<?php

namespace Sovic\Cms\Tests\Ai;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sovic\Cms\Ai\AiResponseParser;
use Sovic\Cms\Ai\Exception\AiResponseException;

final class AiResponseParserTest extends TestCase
{
    #[DataProvider('validProvider')]
    public function testParse(string $json, string $reply, ?string $html): void
    {
        self::assertSame(['reply' => $reply, 'html' => $html], (new AiResponseParser())->parse($json));
    }

    /**
     * @return array<string, array{string, string, string|null}>
     */
    public static function validProvider(): array
    {
        return [
            'with html' => ['{"reply":" Hotovo ","html":"<p>A</p>\n"}', 'Hotovo', '<p>A</p>'],
            'empty html' => ['{"reply":"Jen odpověď","html":""}', 'Jen odpověď', null],
            'null html' => ['{"reply":"Jen odpověď","html":null}', 'Jen odpověď', null],
            'missing html' => ['{"reply":"Jen odpověď"}', 'Jen odpověď', null],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function testInvalid(string $json): void
    {
        $this->expectException(AiResponseException::class);
        (new AiResponseParser())->parse($json);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidProvider(): array
    {
        return [
            'not json' => ['<p>hello</p>'],
            'truncated json' => ['{"reply":"a","html":"<p>'],
            'missing reply' => ['{"html":"<p>A</p>"}'],
            'html not string' => ['{"reply":"a","html":["x"]}'],
            'scalar' => ['"text"'],
        ];
    }
}
