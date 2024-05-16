<?php

namespace Sovic\Cms\Helpers;

use DateTime;
use DateTimeInterface;
use IntlDateFormatter;
use JetBrains\PhpStorm\ArrayShape;

class Date
{
    /** @noinspection SpellCheckingInspection */
    public static function readable(DateTimeInterface $date): string
    {
        $day = (int) $date->format('w');
        $days = [0 => 'neděle', 1 => 'pondělí', 2 => 'úterý', 3 => 'středa', 4 => 'čtvrtek', 5 => 'pátek', 6 => 'sobota'];
        $string = $days[$day];

        $string .= ' ' . $date->format('j. ');
        $month = (int) $date->format('n');
        $months = [1 => 'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července', 'srpna', 'září', 'října', 'listopadu', 'prosince'];
        $string .= $months[$month] . ' ' . $date->format('Y');
        $string .= ', ' . $date->format('G:i') . ' hod.';

        return $string;
    }

    #[ArrayShape(['month' => "string", 'year' => "string", 'title' => "string"])]
    public static function lastMonths(int $count, ?string $locale = null): array
    {
        $date = new DateTime();
        $res = [];
        for ($i = 0; $i < $count; $i++) {
            $month = $date->format('m');
            $year = $date->format('Y');
            $res[] = [
                'month' => (int) $month,
                'year' => (int) $year,
                'title' => $month . '-' . $year,
                'active' => false,
            ];
            if ($locale) {
                $formatter = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::FULL);
                $formatter->setPattern('LLLL y');
                $res[$i]['title'] = $formatter->format($date);
            }
            $date->modify('-1 month');
        }

        return $res;
    }
}
