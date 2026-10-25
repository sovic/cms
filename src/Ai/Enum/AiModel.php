<?php

namespace Sovic\Cms\Ai\Enum;

/**
 * Claude models offered in user AI settings.
 */
enum AiModel: string
{
    case Opus = 'claude-opus-5-5';
    case Sonnet = 'claude-sonnet-5-5';
    case Haiku = 'claude-haiku-5-5';
    case Fable = 'claude-fable-5-1';

    public function getLabel(): string
    {
        return match ($this) {
            self::Opus => 'Claude Opus 5.5 (nejschopnější)',
            self::Sonnet => 'Claude Sonnet 5.5 (vyvážený)',
            self::Haiku => 'Claude Haiku 5.5 (nejrychlejší)',
            self::Fable => 'Claude Fable 5.1',
        };
    }

    /**
     * @return array<string, string> label => model id
     */
    public static function getChoices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->getLabel()] = $case->value;
        }

        return $choices;
    }
}
