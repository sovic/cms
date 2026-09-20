<?php

namespace Sovic\Cms\Ai\Exception;

use Throwable;

/**
 * Error returned by the AI provider (Anthropic API) or its transport.
 */
class AiProviderException extends AiException
{
    public const Authentication = 'authentication';
    public const RateLimit = 'rate_limit';
    public const Overloaded = 'overloaded';
    public const Connection = 'connection';
    public const Other = 'other';

    public function __construct(
        public readonly string $kind,
        string                 $message = '',
        ?Throwable             $previous = null,
    ) {
        parent::__construct($message !== '' ? $message : $kind, 0, $previous);
    }
}
