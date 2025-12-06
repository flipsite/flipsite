<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class FormatHelper
{
    public static function convertToDateFormat(string $date, Language $language, string $format = 'full'): string
    {
        list($year, $month, $day) = explode('-', $date);
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            return date('Y-m-d');
        }
        $timestamp = strtotime($date);
        $pattern = null;
        switch ($format) {
            case 'full': $format = \IntlDateFormatter::FULL;
                break;
            case 'long': $format = \IntlDateFormatter::LONG;
                break;
            case 'medium': $format = \IntlDateFormatter::MEDIUM;
                break;
            case 'short': $format = \IntlDateFormatter::SHORT;
                break;
            case 'none': $format = \IntlDateFormatter::NONE;
                return $date;
                break;
            default:
                $pattern = $format;
                $format = \IntlDateFormatter::NONE;
        }
        $dateFormatter = new \IntlDateFormatter(
            (string)$language,
            $format,
            \IntlDateFormatter::NONE,
            null,
            null,
            $pattern
        );
        return $dateFormatter->format($timestamp);
    }
}
