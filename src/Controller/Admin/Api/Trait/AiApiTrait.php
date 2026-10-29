<?php

namespace Sovic\Cms\Controller\Admin\Api\Trait;

use Sovic\Cms\Ai\Exception\AiException;
use Sovic\Cms\Ai\Exception\AiProviderException;
use Sovic\Cms\Ai\Exception\AiResponseException;
use Sovic\Cms\Ai\Exception\ApiKeyDecryptException;
use Sovic\Cms\Ai\Exception\ComponentLibraryException;
use Sovic\Cms\Ai\Exception\MissingApiKeyException;
use Sovic\Cms\Entity\UserAiSetting;
use Sovic\Cms\Repository\UserAiSettingRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use UserBundle\User\UserEntityInterface;

/**
 * JSON responses of AI assistant API endpoints, messages are translated from the 'ai' domain (api.{error}).
 * For controllers using JsonResponseTrait.
 */
trait AiApiTrait
{
    private TranslatorInterface $aiTranslator;

    #[Required]
    public function setAiTranslator(TranslatorInterface $translator): void
    {
        $this->aiTranslator = $translator;
    }

    /**
     * Fail response when the current user switched the assistant off in AI settings, null otherwise.
     */
    private function failIfAiDisabled(UserEntityInterface $user): ?JsonResponse
    {
        /** @var UserAiSettingRepository $repo */
        $repo = $this->entityManager->getRepository(UserAiSetting::class);
        $setting = $repo->findOneByUser($user);
        if ($setting !== null && !$setting->isEnabled()) {
            return $this->aiFail('disabled', 403, true);
        }

        return null;
    }

    private function aiFailFromException(AiException $e): JsonResponse
    {
        if ($e instanceof MissingApiKeyException || $e instanceof ApiKeyDecryptException) {
            return $this->aiFail('missing_api_key', 400, true);
        }
        if ($e instanceof AiProviderException) {
            return match ($e->kind) {
                AiProviderException::Authentication => $this->aiFail('authentication_error', 400, true),
                AiProviderException::RateLimit => $this->aiFail('rate_limit', 429),
                AiProviderException::Overloaded => $this->aiFail('overloaded', 503),
                AiProviderException::Connection => $this->aiFail('connection_error', 503),
                default => $this->aiFail('provider_error', 502),
            };
        }
        if ($e instanceof AiResponseException) {
            return $this->aiFail($e->reason, 422);
        }
        if ($e instanceof ComponentLibraryException) {
            return $this->aiFail('library_error', 500);
        }

        return $this->aiFail('provider_error', 502);
    }

    private function aiFail(string $error, int $code = 400, bool $withSettingsUrl = false): JsonResponse
    {
        $this->addError($error);
        try {
            $this->data['message'] = $this->aiTranslator->trans('api.' . $error, domain: 'ai');
        } catch (Throwable) {
            $this->data['message'] = $error;
        }
        if ($withSettingsUrl) {
            $this->data['settings_url'] = $this->generateUrl('admin:ai:settings');
        }

        return $this->sendFail($code);
    }
}
