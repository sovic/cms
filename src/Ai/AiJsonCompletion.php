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
 * Single request, single structured (JSON) answer, with the current user API key and model.
 * Base for short one-shot assistants (metadata, summaries), no conversation is stored.
 */
readonly class AiJsonCompletion
{
    public function __construct(
        private EntityManagerInterface           $em,
        private ApiKeyEncryptor                  $encryptor,
        private AnthropicClientFactory           $clientFactory,
        #[Autowire('%ai_model%')] private string $model,
    ) {
    }

    /**
     * @param array $schema JSON schema of the answer object
     * @return array decoded answer
     */
    public function complete(
        UserEntityInterface $user,
        string              $system,
        string              $prompt,
        array               $schema,
        string              $effort = 'low',
        int                 $maxTokens = 2048,
    ): array {
        /** @var UserAiSettingRepository $repo */
        $repo = $this->em->getRepository(UserAiSetting::class);
        $setting = $repo->findOneByUser($user);
        if ($setting === null || !$setting->hasApiKey()) {
            throw new MissingApiKeyException('API key is not set.');
        }
        $apiKey = $this->encryptor->decrypt((string) $setting->getApiKeyEncrypted());

        $model = trim((string) $setting->getModel());

        $result = $this->clientFactory->createMessage($apiKey, [
            'maxTokens' => $maxTokens,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'model' => $model !== '' ? $model : $this->model,
            'outputConfig' => [
                'effort' => $effort,
                'format' => [
                    'type' => 'json_schema',
                    'schema' => $schema,
                ],
            ],
            'system' => $system,
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

        return $data;
    }
}
