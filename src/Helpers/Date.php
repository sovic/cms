<?php

namespace SovicCms\Helpers;

use DateTimeInterface;

class Date
{
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
}
