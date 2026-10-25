<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Ai\AiPageAssistant;
use Sovic\Cms\Ai\AiRequestLock;
use Sovic\Cms\Ai\Exception\AiProviderException;
use Sovic\Cms\Ai\Exception\AiResponseException;
use Sovic\Cms\Ai\Exception\ApiKeyDecryptException;
use Sovic\Cms\Ai\Exception\ComponentLibraryException;
use Sovic\Cms\Ai\Exception\MissingApiKeyException;
use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Entity\AiMessage;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Entity\UserAiSetting;
use Sovic\Cms\Repository\UserAiSettingRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use UserBundle\User\UserEntityInterface;

class AiController extends AbstractBaseApiController
{
    private const MaxMessageLength = 20000;

    private const SettingToggleableFields = [
        'has_api_key',
        'is_enabled',
    ];

    public function __construct(
        EntityManagerInterface                            $entityManager,
        private readonly AiPageAssistant                  $assistant,
        private readonly AiRequestLock                    $lock,
        private readonly TranslatorInterface              $translator,
    ) {
        parent::__construct($entityManager);
    }

    #[Route(
        '/admin/api/web/ai/page/{id}/message',
        name: 'admin:api:web:ai:page:message',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function message(int $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        [$page, $user, $fail] = $this->resolvePageAndUser($id);
        if ($fail !== null) {
            return $fail;
        }

        $data = $this->getRequestData($request);
        $message = trim((string) ($data['message'] ?? ''));
        if ($message === '') {
            return $this->fail('empty_message');
        }
        if (mb_strlen($message) > self::MaxMessageLength) {
            return $this->fail('message_too_long');
        }
        $currentContent = isset($data['current_content']) && is_string($data['current_content'])
            ? $data['current_content']
            : null;

        if ($this->lock->isLocked($page, $user)) {
            return $this->fail('in_progress', 409);
        }

        // Generating a whole page can take tens of seconds. The answer is stored at the end of the call, so keep
        // running even when the browser or a proxy gave up already - the client picks the answer up from history.
        set_time_limit(180);
        ignore_user_abort(true);

        $this->lock->acquire($page, $user);

        try {
            $response = $this->assistant->send($page, $user, $message, $currentContent);
        } catch (MissingApiKeyException|ApiKeyDecryptException) {
            return $this->fail('missing_api_key', 400, true);
        } catch (AiProviderException $e) {
            return match ($e->kind) {
                AiProviderException::Authentication => $this->fail('authentication_error', 400, true),
                AiProviderException::RateLimit => $this->fail('rate_limit', 429),
                AiProviderException::Overloaded => $this->fail('overloaded', 503),
                AiProviderException::Connection => $this->fail('connection_error', 503),
                default => $this->fail('provider_error', 502),
            };
        } catch (AiResponseException $e) {
            return $this->fail($e->reason, 422);
        } catch (ComponentLibraryException) {
            return $this->fail('library_error', 500);
        } finally {
            $this->lock->release($page, $user);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        $this->data['reply'] = $response->reply;
        $this->data['html'] = $response->html;
        $this->data['message_id'] = $response->messageId;
        $this->data['usage'] = [
            'input_tokens' => $response->inputTokens,
            'output_tokens' => $response->outputTokens,
            'cache_read_input_tokens' => $response->cacheReadInputTokens,
        ];

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/web/ai/page/{id}/history',
        name: 'admin:api:web:ai:page:history',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function history(int $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        [$page, $user, $fail] = $this->resolvePageAndUser($id);
        if ($fail !== null) {
            return $fail;
        }

        $messages = $this->assistant->getHistory($page, $user);

        // used by the client to pick up an answer of a request that timed out in the browser or a proxy
        $afterId = (int) $request->query->get('after_id', 0);
        if ($afterId > 0) {
            $messages = array_filter($messages, static fn(AiMessage $message) => $message->getId() > $afterId);
        }

        $this->data['in_progress'] = $this->lock->isLocked($page, $user);
        $this->data['messages'] = array_values(array_map(
            static fn(AiMessage $message) => [
                'id' => $message->getId(),
                'role' => $message->getRole()->value,
                'content' => $message->getContent(),
                'html' => $message->getHtml(),
                'created_at' => $message->getCreatedAt()->format(self::DateFormat),
            ],
            $messages,
        ));

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/web/ai/page/{id}/reset',
        name: 'admin:api:web:ai:page:reset',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function reset(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        [$page, $user, $fail] = $this->resolvePageAndUser($id);
        if ($fail !== null) {
            return $fail;
        }

        $conversation = $this->assistant->reset($page, $user);
        $this->data['conversation_id'] = $conversation->getId();

        return $this->sendSuccess();
    }

    /**
     * Toggles of current user AI settings (settings page sidebar).
     * has_api_key can be only switched off, it removes the stored key.
     */
    #[Route(
        '/admin/api/web/ai/settings/toggle-state',
        name: 'admin:api:web:ai:settings:toggle-state',
        methods: ['POST'],
    )]
    public function toggleSettingState(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        if (!$user instanceof UserEntityInterface) {
            throw $this->createAccessDeniedException();
        }

        $data = $this->getRequestData($request);
        $field = $data['field'] ?? null;
        if (!in_array($field, self::SettingToggleableFields, true)) {
            $this->addError('invalid_field');

            return $this->sendFail();
        }

        $state = (bool) ($data['state'] ?? false);

        /** @var UserAiSettingRepository $repo */
        $repo = $this->entityManager->getRepository(UserAiSetting::class);
        $setting = $repo->findOneByUser($user) ?? new UserAiSetting($user);

        if ($field === 'has_api_key') {
            if ($state) {
                $this->addError('invalid_state');

                return $this->sendFail();
            }
            $setting->setApiKeyEncrypted(null);
            $setting->setApiKeyHint(null);
            $value = $setting->hasApiKey();
        } else {
            $setting->setIsEnabled($state);
            $value = $setting->isEnabled();
        }

        $setting->setUpdatedAt(new DateTimeImmutable());
        $this->entityManager->persist($setting);
        $this->entityManager->flush();

        $this->data['value'] = $value;

        return $this->sendSuccess();
    }

    /**
     * @return array{0: Page|null, 1: UserEntityInterface|null, 2: JsonResponse|null}
     */
    private function resolvePageAndUser(int $id): array
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntityInterface) {
            throw $this->createAccessDeniedException();
        }

        /** @var UserAiSettingRepository $settingRepo */
        $settingRepo = $this->entityManager->getRepository(UserAiSetting::class);
        $setting = $settingRepo->findOneByUser($user);
        if ($setting !== null && !$setting->isEnabled()) {
            return [null, null, $this->fail('disabled', 403, true)];
        }

        $page = $this->entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            return [null, null, $this->fail('not_found', 404)];
        }

        return [$page, $user, null];
    }

    private function fail(string $error, int $code = 400, bool $withSettingsUrl = false): JsonResponse
    {
        $this->addError($error);
        try {
            $this->data['message'] = $this->translator->trans('api.' . $error, domain: 'ai');
        } catch (Throwable) {
            $this->data['message'] = $error;
        }
        if ($withSettingsUrl) {
            $this->data['settings_url'] = $this->generateUrl('admin:ai:settings');
        }

        return $this->sendFail($code);
    }
}
