<?php

namespace Sovic\Cms\Ai;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Ai\Dto\AiResponse;
use Sovic\Cms\Ai\Dto\PageContext;
use Sovic\Cms\Ai\Enum\AiMessageRole;
use Sovic\Cms\Ai\Exception\AiResponseException;
use Sovic\Cms\Ai\Exception\MissingApiKeyException;
use Sovic\Cms\Entity\AiConversation;
use Sovic\Cms\Entity\AiMessage;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Entity\UserAiSetting;
use Sovic\Cms\Repository\AiConversationRepository;
use Sovic\Cms\Repository\AiMessageRepository;
use Sovic\Cms\Repository\UserAiSettingRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use UserBundle\User\UserEntityInterface;

/**
 * Chat assistant preparing page body HTML from the website component library.
 */
readonly class AiPageAssistant
{
    public function __construct(
        private EntityManagerInterface                $em,
        private ApiKeyEncryptor                       $encryptor,
        private ComponentLibraryLoader                $libraryLoader,
        private PromptBuilder                         $promptBuilder,
        private AiResponseParser                      $parser,
        private HtmlSanitizer                         $sanitizer,
        private AnthropicClientFactory                $clientFactory,
        #[Autowire('%ai_model%')] private string      $model,
        #[Autowire('%ai_effort%')] private string     $effort,
        #[Autowire('%ai_max_tokens%')] private int    $maxTokens,
        #[Autowire('%ai_history_limit%')] private int $historyLimit,
    ) {
    }

    public function send(Page $page, UserEntityInterface $user, string $message, ?string $currentContent = null): AiResponse
    {
        $setting = $this->getSettingRepository()->findOneByUser($user);
        if ($setting === null || !$setting->hasApiKey()) {
            throw new MissingApiKeyException('API key is not set.');
        }
        $apiKey = $this->encryptor->decrypt((string) $setting->getApiKeyEncrypted());

        $conversation = $this->getConversationRepository()->findLatestByPageAndUser($page, $user);
        $history = $conversation ? $this->getMessageRepository()->findLastByConversation($conversation, $this->historyLimit) : [];

        $library = $this->libraryLoader->load();
        $system = $this->promptBuilder->buildSystemPrompt($library);
        $messages = $this->promptBuilder->buildMessages($history, $message, PageContext::fromPage($page, $currentContent));

        $result = $this->clientFactory->createMessage($apiKey, [
            'maxTokens' => $this->maxTokens,
            'messages' => $messages,
            'model' => $this->getModel($setting),
            'outputConfig' => [
                'effort' => $this->effort,
                'format' => [
                    'type' => 'json_schema',
                    'schema' => $this->promptBuilder->outputSchema(),
                ],
            ],
            'system' => [
                [
                    'type' => 'text',
                    'text' => $system,
                    'cacheControl' => ['type' => 'ephemeral'],
                ],
            ],
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

        $parsed = $this->parser->parse($text);
        $html = $parsed['html'] !== null ? $this->sanitizer->sanitize($parsed['html']) : null;
        if ($html === '') {
            $html = null;
        }

        if ($conversation === null) {
            $conversation = new AiConversation($page, $user);
            $this->em->persist($conversation);
        }
        $conversation->setUpdatedAt(new DateTimeImmutable());

        $userMessage = new AiMessage($conversation, AiMessageRole::User, trim($message));
        $this->em->persist($userMessage);

        $assistantMessage = new AiMessage($conversation, AiMessageRole::Assistant, $parsed['reply'], $html);
        $assistantMessage->setInputTokens(
            $result->usage->inputTokens
            + (int) $result->usage->cacheReadInputTokens
            + (int) $result->usage->cacheCreationInputTokens
        );
        $assistantMessage->setOutputTokens($result->usage->outputTokens);
        $this->em->persist($assistantMessage);

        $this->em->flush();

        return new AiResponse(
            $parsed['reply'],
            $html,
            $assistantMessage->getId(),
            $result->usage->inputTokens,
            $result->usage->outputTokens,
            (int) $result->usage->cacheReadInputTokens,
        );
    }

    /**
     * @return AiMessage[] messages of the current conversation, oldest first
     */
    public function getHistory(Page $page, UserEntityInterface $user): array
    {
        $conversation = $this->getConversationRepository()->findLatestByPageAndUser($page, $user);
        if ($conversation === null) {
            return [];
        }

        return $this->getMessageRepository()->findByConversation($conversation);
    }

    /**
     * Starts a new, empty conversation, previous ones are kept in the database.
     */
    public function reset(Page $page, UserEntityInterface $user): AiConversation
    {
        $conversation = new AiConversation($page, $user);
        $this->em->persist($conversation);
        $this->em->flush();

        return $conversation;
    }

    private function getModel(UserAiSetting $setting): string
    {
        $model = trim((string) $setting->getModel());

        return $model !== '' ? $model : $this->model;
    }

    private function getSettingRepository(): UserAiSettingRepository
    {
        /** @var UserAiSettingRepository */
        return $this->em->getRepository(UserAiSetting::class);
    }

    private function getConversationRepository(): AiConversationRepository
    {
        /** @var AiConversationRepository */
        return $this->em->getRepository(AiConversation::class);
    }

    private function getMessageRepository(): AiMessageRepository
    {
        /** @var AiMessageRepository */
        return $this->em->getRepository(AiMessage::class);
    }
}
