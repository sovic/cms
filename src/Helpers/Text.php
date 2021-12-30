<?php

namespace SovicCms\Helpers;

use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validation;

class Text
{
    public static function stripEmoji($text): string
    {
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $cleanText = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $cleanText = preg_replace($regexSymbols, '', $cleanText);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $cleanText = preg_replace($regexTransport, '', $cleanText);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $cleanText = preg_replace($regexMisc, '', $cleanText);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $cleanText = preg_replace($regexDingbats, '', $cleanText);

        // Match Flags
        $regexDingbats = '/[\x{1F1E6}-\x{1F1FF}]/u';
        $cleanText = preg_replace($regexDingbats, '', $cleanText);

        // Others
        $regexDingbats = '/[\x{1F910}-\x{1F95E}]/u';
        $cleanText = preg_replace($regexDingbats, '', $cleanText);

        $regexDingbats = '/[\x{1F980}-\x{1F991}]/u';
        $cleanText = preg_replace($regexDingbats, '', $cleanText);

        $regexDingbats = '/[\x{1F9C0}]/u';
        $cleanText = preg_replace($regexDingbats, '', $cleanText);

        $regexDingbats = '/[\x{1F9F9}]/u';
        $cleanText = preg_replace($regexDingbats, '', $cleanText);

        return trim($cleanText);
    }

    public static function isUrl(string $text): bool
    {
        $violations = Validation::createValidator()->validate($text, new Url());

        return (0 === count($violations));
    }

    public static function prettyText(string $text): string
    {
        // TODO regex
        return (string) str_replace(
            [' a ', ' s ', ' k ', ' o ', ' %', ' od ', ' do ', ' se ', ' na ', ' ke '],
            [
                ' a&nbsp;',
                ' s&nbsp;',
                ' k&nbsp;',
                ' o&nbsp;',
                '&nbsp;%',
                ' od&nbsp;',
                ' do&nbsp;',
                ' se&nbsp;',
                ' na&nbsp;',
                ' ke&nbsp;',
            ],
            $text
        );
    }
}
