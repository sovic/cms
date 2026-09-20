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
     */
    public const Timeout = 170;

    public function create(string $apiKey): Client
    {
        $transporter = new Psr18Client(HttpClient::create([
            'timeout' => self::Timeout,
            'max_duration' => self::Timeout,
        ]));

        return new Client(
            apiKey: $apiKey,
            requestOptions: RequestOptions::with(
                maxRetries: 1,
                transporter: $transporter,
            ),
        );
    }
}
