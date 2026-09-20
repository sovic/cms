<?php

namespace Sovic\Cms\Ai;

use JsonException;
use Sovic\Cms\Ai\Exception\AiResponseException;

class AiResponseParser
{
    /**
     * @return array{reply: string, html: string|null}
     */
    public function parse(string $json): array
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new AiResponseException(AiResponseException::InvalidResponse, 'Response is not valid JSON.', $e);
        }

        if (!is_array($data) || !isset($data['reply']) || !is_string($data['reply'])) {
            throw new AiResponseException(AiResponseException::InvalidResponse, 'Response is missing "reply".');
        }

        $html = $data['html'] ?? null;
        if ($html !== null && !is_string($html)) {
            throw new AiResponseException(AiResponseException::InvalidResponse, 'Response "html" must be a string.');
        }
        $html = $html !== null ? trim($html) : null;

        return [
            'reply' => trim($data['reply']),
            'html' => $html === '' ? null : $html,
        ];
    }
}
