<?php

namespace Sovic\Cms\Email;

class EmailTheme implements EmailThemeInterface
{
    protected function getPrimaryColor(): string
    {
        return '#0078A2';
    }

    protected function getSecondaryColor(): string
    {
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
            'cta_button_style' => 'Font-family: ' . $fontFamily . '; Box-sizing: border-box; Display: inline-block; Cursor: pointer; Color: #ffffff; Background: ' . $primaryColor . '; Border-radius: 5px; Font-size: 14px; Font-weight: bold; Margin: 0; Padding: 10px 25px 10px 25px !important; Text-decoration: none;',
            'paragraph_style' => $paragraphStyle,
            'last_paragraph_style' => $paragraphStyle . ' Margin-bottom: 0;',
            'footer_link_style' => 'Color: #676f7e; Font-family: ' . $fontFamily . '; Font-size: 0.875rem; Font-weight: 400; Line-height: 1.5; Margin: 0 0 1.5rem; Margin-bottom: 0;',
        ];
    }

    public function getFormattedHtml(string $html): string
    {
        $themeData = $this->getTheme();

        // replace <a class="cta_button"> with styled button
        $ctaButtonStyle = $themeData['cta_button_style'] ?? '';
        /** @noinspection HtmlDeprecatedAttribute */
        /** @noinspection HtmlUnknownAttribute */
        $replacement = '
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                <tbody>
                <tr>
                    <td align="left">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                                <td style="">
                                    <a style="' . $ctaButtonStyle . '"$1>$2</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        ';
        $html = preg_replace('/<a class="int_cta_link"(.*?)>(.*?)<\/a>/s', $replacement, $html);

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
