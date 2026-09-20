<?php

namespace Sovic\Cms\Ai;

use Sovic\Cms\Ai\Dto\ComponentLibrary;
use Sovic\Cms\Ai\Dto\PageContext;
use Sovic\Cms\Ai\Enum\AiMessageRole;
use Sovic\Cms\Entity\AiMessage;

/**
 * Builds the system prompt, conversation messages and output schema for the AI page assistant.
 * The system prompt only depends on the component library, so it stays byte-stable and cacheable.
 */
class PromptBuilder
{
    /**
     * @noinspection HtmlRequiredLangAttribute
     * @noinspection JSXDomNesting
     */
    public function buildSystemPrompt(ComponentLibrary $library): string
    {
        $prompt = <<<'PROMPT'
You are a web content assistant inside a CMS administration. You help editors who do not know HTML to write and
structure the body content of website pages. The HTML you produce is inserted into a TinyMCE editor and rendered
inside the website's existing layout (header, navigation and footer are already provided by the site), so the
page must look consistent with the rest of the website.

How to build page HTML:
- Compose the page only from the components listed in <components> below. Copy their markup structure and CSS
  classes exactly and replace only the texts, links, image sources and the number of repeated items. Plain
  headings (h2-h4), paragraphs, lists, links, strong/em are fine inside and between components.
- Follow the rules in <styleguide>. If the styleguide and a user request conflict, follow the user and mention it
  briefly in your reply.
- Do not invent new CSS classes and do not use <script>, <style>, inline style attributes, event handler
  attributes, <form> elements or <html>/<head>/<body> wrappers. Do not add an <h1>, the page title is rendered by
  the layout.
- Write the page texts in the page language given in <page_context>, unless the user asks otherwise. Use the
  editor's own text where it is provided; you may correct typos and improve structure, but do not invent facts
  (prices, dates, names, contacts). Where information is missing, leave a clear placeholder like [doplnit telefon]
  and mention it in your reply.
- For images use the URLs the user provides; if none is provided, keep the component's placeholder image source.

How to answer:
- Respond with the JSON object required by the output schema.
- "reply": a short message for the editor in the language they write in: what you did, which components you used,
  anything they should check or fill in. Plain text, no HTML, no Markdown.
- "html": when the page content should change, the complete new page body HTML (not only the changed part, keep
  the parts of <current_content> the editor did not ask to change). When the editor only asks a question or
  nothing should change, return an empty string.
PROMPT;

        $prompt .= "\n\n<styleguide>\n" . ($library->styleguide !== '' ? $library->styleguide : '(no styleguide provided)') . "\n</styleguide>\n\n<components>\n";
        foreach ($library->components as $name => $html) {
            $prompt .= '<component name="' . htmlspecialchars($name, ENT_QUOTES) . "\">\n" . $html . "\n</component>\n";
        }
        $prompt .= '</components>';

        return $prompt;
    }

    /**
     * @param AiMessage[] $history previous messages, oldest first
     * @return array<int, array{role: string, content: string}>
     */
    public function buildMessages(array $history, string $userMessage, PageContext $context): array
    {
        $messages = [];
        foreach ($history as $message) {
            if ($message->getRole() === AiMessageRole::Assistant) {
                // conversation must start with a user message (history may be cut by the limit)
                if (empty($messages)) {
                    continue;
                }
                $messages[] = [
                    'role' => 'assistant',
                    'content' => json_encode(
                        [
                            'reply' => $message->getContent(),
                            'html' => $message->getHtml() ?? '',
                        ],
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                ];
                continue;
            }
            $messages[] = [
                'role' => 'user',
                'content' => $message->getContent(),
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $this->buildUserTurn($userMessage, $context),
        ];

        return $messages;
    }

    public function buildUserTurn(string $userMessage, PageContext $context): string
    {
        $content = trim($context->content);

        return "<page_context>\n"
            . '<page_name>' . $context->name . "</page_name>\n"
            . '<page_heading>' . $context->heading . "</page_heading>\n"
            . '<page_language>' . $context->lang . "</page_language>\n"
            . "<current_content>\n" . ($content !== '' ? $content : '(empty page)') . "\n</current_content>\n"
            . "</page_context>\n\n"
            . "<request>\n" . trim($userMessage) . "\n</request>";
    }

    public function outputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'reply' => [
                    'type' => 'string',
                    'description' => 'Short plain text message for the editor.',
                ],
                'html' => [
                    'type' => 'string',
                    'description' => 'Complete new page body HTML, or an empty string when the content should not change.',
                ],
            ],
            'required' => ['reply', 'html'],
            'additionalProperties' => false,
        ];
    }
}
