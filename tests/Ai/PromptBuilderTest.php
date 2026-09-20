<?php

namespace Sovic\Cms\Tests\Ai;

use PHPUnit\Framework\TestCase;
use Sovic\Cms\Ai\Dto\ComponentLibrary;
use Sovic\Cms\Ai\Dto\PageContext;
use Sovic\Cms\Ai\Enum\AiMessageRole;
use Sovic\Cms\Ai\PromptBuilder;
use Sovic\Cms\Entity\AiConversation;
use Sovic\Cms\Entity\AiMessage;

final class PromptBuilderTest extends TestCase
{
    public function testSystemPromptContainsLibrary(): void
    {
        $prompt = (new PromptBuilder())->buildSystemPrompt(new ComponentLibrary(
            'Use hero once.',
            ['hero' => '<section class="hero"></section>', 'cta' => '<a class="btn-cta"></a>'],
        ));

        self::assertStringContainsString("<styleguide>\nUse hero once.\n</styleguide>", $prompt);
        self::assertStringContainsString("<component name=\"hero\">\n<section class=\"hero\"></section>\n</component>", $prompt);
        self::assertStringContainsString('<component name="cta">', $prompt);
        self::assertStringContainsString('<script>', $prompt);
    }

    public function testSystemPromptIsStable(): void
    {
        $builder = new PromptBuilder();
        $library = new ComponentLibrary('', ['hero' => '<div></div>']);

        self::assertSame($builder->buildSystemPrompt($library), $builder->buildSystemPrompt($library));
    }

    public function testBuildMessages(): void
    {
        $conversation = $this->createStub(AiConversation::class);
        $history = [
            new AiMessage($conversation, AiMessageRole::Assistant, 'orphan reply cut by history limit'),
            new AiMessage($conversation, AiMessageRole::User, 'Napiš stránku O nás'),
            new AiMessage($conversation, AiMessageRole::Assistant, 'Hotovo', '<p>O nás</p>'),
        ];
        $context = new PageContext('O nás', 'O naší firmě', 'cs', '<p>Stávající obsah</p>');

        $messages = (new PromptBuilder())->buildMessages($history, 'Zkrať to', $context);

        self::assertCount(3, $messages);
        self::assertSame(['user', 'assistant', 'user'], array_column($messages, 'role'));
        self::assertSame('Napiš stránku O nás', $messages[0]['content']);
        self::assertSame(['reply' => 'Hotovo', 'html' => '<p>O nás</p>'], json_decode($messages[1]['content'], true));
        self::assertStringContainsString("<current_content>\n<p>Stávající obsah</p>\n</current_content>", $messages[2]['content']);
        self::assertStringContainsString('<page_language>cs</page_language>', $messages[2]['content']);
        self::assertStringEndsWith("<request>\nZkrať to\n</request>", $messages[2]['content']);
    }

    public function testEmptyPageContent(): void
    {
        $turn = (new PromptBuilder())->buildUserTurn('Ahoj', new PageContext('A', '', 'cs', ''));

        self::assertStringContainsString('(empty page)', $turn);
    }

    public function testOutputSchema(): void
    {
        $schema = (new PromptBuilder())->outputSchema();

        self::assertFalse($schema['additionalProperties']);
        self::assertSame(['reply', 'html'], $schema['required']);
    }
}
