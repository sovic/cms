<?php

namespace Sovic\Cms\Ai\Exception;

use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\ErrorType;
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

    public static function fromApiException(APIException $e): self
    {
        if ($e instanceof APIStatusException) {
            $kind = match ($e->type) {
                ErrorType::AUTHENTICATION_ERROR, ErrorType::PERMISSION_ERROR => self::Authentication,
                ErrorType::RATE_LIMIT_ERROR => self::RateLimit,
                ErrorType::OVERLOADED_ERROR => self::Overloaded,
                default => match ($e->status) {
                    401, 403 => self::Authentication,
                    429 => self::RateLimit,
                    529 => self::Overloaded,
                    default => self::Other,
                },
            };

            return new self($kind, $e->type?->value ?? 'status_' . $e->status, $e);
        }
        if ($e instanceof APIConnectionException) {
            return new self(self::Connection, 'connection_error', $e);
        }

        return new self(self::Other, 'api_error', $e);
    }
}
