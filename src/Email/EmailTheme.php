<?php

namespace Sovic\Cms\Email;

use Sovic\Common\Project\SettingGroupId;
use Sovic\Common\Project\Settings;

readonly class EmailTheme implements EmailThemeInterface
{
    /**
     * @param array<string, mixed> $options keyed by EmailThemeOption values, e.g. ['cta_button_align' => 'center']
     */
    public function __construct(
        private Settings $settings,
        private array    $options = [],
    ) {
    }

    protected function getOption(EmailThemeOption $option): mixed
    {
        $value = $this->options[$option->value] ?? null;
        if ($value === null) {
            return $option->defaultValue();
        }

        $allowedValues = $option->allowedValues();
        if ($allowedValues !== null && !in_array($value, $allowedValues, true)) {
            return $option->defaultValue();
        }

        return $value;
    }

    protected function getPrimaryColor(): string
    {
        $primaryColor = $this->settings->get(SettingGroupId::Mailer->value, 'primary_color');
        if (!empty($primaryColor)) {
            return $primaryColor;
        }

        return '#0078A2';
    }

    protected function getSecondaryColor(): string
    {
        $secondaryColor = $this->settings->get(SettingGroupId::Mailer->value, 'secondary_color');
        if (!empty($secondaryColor)) {
            return $secondaryColor;
        }

        return '#e3001a';
    }

    public function getTheme(): array
    {
        $primaryColor = $this->getPrimaryColor();
        $secondaryColor = $this->getSecondaryColor();
        $fontFamily = "'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif";
        $paragraphStyle = 'Font-family: ' . $fontFamily . '; Font-size: 1rem; Font-weight: 400; Line-height: 1.5; Margin: 0 0 1.5rem;';

        return [
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
            'font_family' => $fontFamily,
            'link_style' => 'Box-sizing: border-box; Color: ' . $primaryColor . '; Text-decoration: underline;',
            'cta_button_align' => $this->getOption(EmailThemeOption::CtaButtonAlign),
            'cta_button_style' => 'Font-family: ' . $fontFamily . '; Box-sizing: border-box; Display: inline-block; Cursor: pointer; Color: #ffffff; Background: ' . $primaryColor . '; Border-radius: 5px; Font-size: 14px; Font-weight: bold; Margin: 0; Padding: 10px 25px 10px 25px !important; Text-decoration: none;',
            'paragraph_style' => $paragraphStyle,
            'last_paragraph_style' => $paragraphStyle . ' Margin-bottom: 0;',
            'footer_link_style' => 'Color: #676f7e; Font-family: ' . $fontFamily . '; Font-size: 0.875rem; Font-weight: 400; Line-height: 1.5; Margin: 0 0 1.5rem; Margin-bottom: 0;',
        ];
    }

    public function getFormattedHtml(string $html): string
    {
        $themeData = $this->getTheme();

        // replace it <a class="cta_button"> with styled button
        $ctaButtonStyle = $themeData['cta_button_style'] ?? '';
        $ctaButtonAlign = $themeData['cta_button_align'] ?? 'left';
        // layout sets "table { margin: 0 auto !important; }", override inline to keep the alignment
        $ctaTableMargin = match ($ctaButtonAlign) {
            'center' => '0 auto',
            'right' => '0 0 0 auto',
            default => '0',
        };
        /** @noinspection HtmlDeprecatedAttribute */
        /** @noinspection HtmlUnknownAttribute */
        $template = '
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                <tbody>
                <tr>
                    <td align="' . $ctaButtonAlign . '" style="text-align: ' . $ctaButtonAlign . ';">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: ' . $ctaTableMargin . ' !important;">
                            <tbody>
                            <tr>
                                <td style="">
                                    <a style="' . $ctaButtonStyle . '"%attributes%>%content%</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        ';

        // class can be anywhere among the attributes (editor / sanitizer may reorder them)
        $html = preg_replace_callback(
            '/<a\b([^>]*)>(.*?)<\/a>/s',
            static function (array $matches) use ($template): string {
                $attributes = $matches[1];
                $classPattern = '/\s+class\s*=\s*(["\'])([^"\']*)\1/i';
                if (preg_match($classPattern, $attributes, $classMatch) !== 1) {
                    return $matches[0];
                }
                $classes = preg_split('/\s+/', trim($classMatch[2])) ?: [];
                if (!in_array('int_cta_link', $classes, true)) {
                    return $matches[0];
                }
                // drop class and any style, the button has its own style
                $attributes = preg_replace([$classPattern, '/\s+style\s*=\s*(["\'])[^"\']*\1/i'], '', $attributes) ?? '';
                $attributes = rtrim($attributes);

                return strtr($template, [
                    '%attributes%' => $attributes,
                    '%content%' => $matches[2],
                ]);
            },
            $html,
        ) ?? $html;

        // update links style, only without style attribute
        $linkStyle = $themeData['link_style'] ?? '';
        $html = preg_replace('/<a (?![^>]*style=)(.*?)>/s', '<a style="' . $linkStyle . '" $1>', $html);

        // h1 -> h1.h2
        $html = preg_replace('/<h1>/', '<h1 class="h2">', $html);

        // update paragraph style
        $paragraphStyle = $themeData['paragraph_style'] ?? '';
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $html = str_replace('<p>', '<p style="' . $paragraphStyle . '">', $html);

        return $html;
    }

    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    public function getFormattedFooterHtml(string $html): string
    {
        $themeData = $this->getTheme();

        // update footer link style
        $footerLinkStyle = $themeData['footer_link_style'] ?? '';
        $html = preg_replace('/<a (?![^>]*style=)(.*?)>/s', '<a style="' . $footerLinkStyle . '" $1>', $html);

        return $html;
    }
}
