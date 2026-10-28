<?php

namespace Sovic\Cms\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIException;
use Anthropic\Messages\Message;
use Anthropic\RequestOptions;
use Sovic\Cms\Ai\Exception\AiProviderException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class AnthropicClientFactory
{
    /**
     * Seconds, the SDK leaves timeouts to the transport and Symfony HttpClient defaults to 60 s idle timeout,
     * which is not enough for longer page generations.
     *
     * Each layer above this one must allow more time, see README (PHP, PHP-FPM, nginx).
     */
    public const Timeout = 150;

    public function create(string $apiKey): Client
    {
        $transporter = new Psr18Client(HttpClient::create([
            'timeout' => self::Timeout,
            'max_duration' => self::Timeout,
        ]));

        return new Client(
            apiKey: $apiKey,
            requestOptions: RequestOptions::with(
                // no retries, the SDK retries transport timeouts as well and that would double the wall time
                maxRetries: 0,
                transporter: $transporter,
            ),
        );
    }

    /**
     * Sends one Messages API request, provider errors are converted to AiProviderException.
     * The API key is wiped from memory afterward.
     *
     * @param array<string, mixed> $params named arguments of MessagesService::create()
     */
    public function createMessage(string &$apiKey, array $params): Message
    {
        try {
            return $this->create($apiKey)->messages->create(...$params);
        } catch (APIException $e) {
            throw AiProviderException::fromApiException($e);
        } finally {
            sodium_memzero($apiKey);
        }
    }
}
