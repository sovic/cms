<?php

namespace Sovic\Cms\Ai\Dto;

final readonly class AiResponse
{
    public function __construct(
        public string  $reply,
        public ?string $html,
        public int     $messageId,
        public int     $inputTokens,
        public int     $outputTokens,
        public int     $cacheReadInputTokens,
    ) {
    }
}
