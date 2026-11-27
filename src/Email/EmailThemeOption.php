<?php

namespace Sovic\Cms\Email;

/**
 * Options of EmailTheme, passed as an array keyed by the case value:
 * new EmailTheme($settings, [EmailThemeOption::CtaButtonAlign->value => 'center'])
 */
enum EmailThemeOption: string
{
    // CTA button (int_cta_link) alignment
    case CtaButtonAlign = 'cta_button_align';

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection */
    public function defaultValue(): mixed
    {
        return match ($this) {
            self::CtaButtonAlign => 'left',
        };
    }

    /**
     * @return array<mixed>|null null when any value is allowed
     * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
     */
    public function allowedValues(): ?array
    {
        return match ($this) {
            self::CtaButtonAlign => ['left', 'center', 'right'],
        };
    }
}
