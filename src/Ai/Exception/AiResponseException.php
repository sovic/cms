<?php

namespace Sovic\Cms\Ai\Exception;

use Throwable;

class AiResponseException extends AiException
{
    public const Refusal = 'refusal';
    public const Truncated = 'truncated';
    public const InvalidResponse = 'invalid_response';

    public function __construct(
        public readonly string $reason,
        string                 $message = '',
        ?Throwable             $previous = null,
    ) {
        parent::__construct($message !== '' ? $message : $reason, 0, $previous);
    }
}
