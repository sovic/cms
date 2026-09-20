<?php

namespace Sovic\Cms\Ai;

use Anthropic\Client;
use Anthropic\RequestOptions;
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
}
