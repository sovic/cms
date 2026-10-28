<?php

namespace Sovic\Cms\Ai;

use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Sovic\Cms\Ai\Exception\AiResponseException;
use Sovic\Cms\Ai\Exception\MissingApiKeyException;
use Sovic\Cms\Entity\UserAiSetting;
use Sovic\Cms\Repository\UserAiSettingRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use UserBundle\User\UserEntityInterface;

/**
 * One-shot suggestion of SEO meta title, description and keywords from the edited content.
 */
readonly class AiMetadataAssistant
{
    public const Fields = ['title', 'description', 'keywords'];

    public const TitleMaxLength = 60;
    public const DescriptionMaxLength = 155;
    public const KeywordsMax = 5;

    /**
     * Characters of plain text content sent to the model, the beginning is enough for metadata.
     */
    private const ContentMaxLength = 3000;

    /**
     * Database column length of all meta fields.
     */
    private const ColumnLength = 255;

    private const MaxTokens = 2048;

    public function __construct(
        private EntityManagerInterface           $em,
        private ApiKeyEncryptor                  $encryptor,
        private AnthropicClientFactory           $clientFactory,
        #[Autowire('%ai_model%')] private string $model,
    ) {
    }

    /**
     * @param string[] $fields requested fields, subset of self::Fields
     * @return array<string, string> suggested values by field
     */
    public function generate(UserEntityInterface $user, array $fields, string $heading, string $content, string $tags): array
    {
        $fields = array_values(array_intersect(self::Fields, $fields));
        if (empty($fields)) {
            return [];
        }

        /** @var UserAiSettingRepository $repo */
        $repo = $this->em->getRepository(UserAiSetting::class);
        $setting = $repo->findOneByUser($user);
        if ($setting === null || !$setting->hasApiKey()) {
            throw new MissingApiKeyException('API key is not set.');
        }
        $apiKey = $this->encryptor->decrypt((string) $setting->getApiKeyEncrypted());

        $model = trim((string) $setting->getModel());

        $result = $this->clientFactory->createMessage($apiKey, [
            'maxTokens' => self::MaxTokens,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->buildPrompt($heading, $content, $tags),
                ],
            ],
            'model' => $model !== '' ? $model : $this->model,
            'outputConfig' => [
                'effort' => 'low',
                'format' => [
                    'type' => 'json_schema',
                    'schema' => $this->outputSchema($fields),
                ],
            ],
            'system' => 'Write SEO metadata for a web page, in the language of the page. '
                . 'Title max ' . self::TitleMaxLength . ' characters, description max ' . self::DescriptionMaxLength
                . ' characters, max ' . self::KeywordsMax . ' keywords.',
        ]);

        if ($result->stopReason === 'refusal') {
            throw new AiResponseException(AiResponseException::Refusal);
        }
        if ($result->stopReason === 'max_tokens') {
            throw new AiResponseException(AiResponseException::Truncated);
        }

        $text = null;
        foreach ($result->content as $block) {
            if ($block->type === 'text') {
                $text = $block->text;
                break;
            }
        }
        if ($text === null) {
            throw new AiResponseException(AiResponseException::InvalidResponse, 'Response has no text block.');
        }

        try {
            $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new AiResponseException(AiResponseException::InvalidResponse, 'Response is not valid JSON.', $e);
        }
        if (!is_array($data)) {
            throw new AiResponseException(AiResponseException::InvalidResponse, 'Response is not an object.');
        }

        $values = [];
        foreach ($fields as $field) {
            $value = $data[$field] ?? null;
            if ($field === 'keywords' && is_array($value)) {
                $keywords = array_filter(array_map(static fn($keyword) => trim((string) $keyword), $value));
                $value = implode(', ', array_slice(array_unique($keywords), 0, self::KeywordsMax));
            }
            if (!is_string($value) || trim($value) === '') {
                continue;
            }
            $values[$field] = mb_substr(trim($value), 0, self::ColumnLength);
        }

        return $values;
    }

    private function buildPrompt(string $heading, string $content, string $tags): string
    {
        $content = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5)));
        if (mb_strlen($content) > self::ContentMaxLength) {
            $content = mb_substr($content, 0, self::ContentMaxLength) . '…';
        }

        $prompt = '';
        if (trim($heading) !== '') {
            $prompt .= '<heading>' . trim($heading) . "</heading>\n";
        }
        if (trim($tags) !== '') {
            $prompt .= '<tags>' . trim($tags) . "</tags>\n";
        }
        if ($content !== '') {
            $prompt .= '<content>' . $content . "</content>\n";
        }

        return $prompt;
    }

    /**
     * @param string[] $fields
     */
    private function outputSchema(array $fields): array
    {
        $properties = [
            'title' => ['type' => 'string'],
            'description' => ['type' => 'string'],
            'keywords' => ['type' => 'array', 'items' => ['type' => 'string']],
        ];

        return [
            'type' => 'object',
            'properties' => array_intersect_key($properties, array_flip($fields)),
            'required' => $fields,
            'additionalProperties' => false,
        ];
    }
}
