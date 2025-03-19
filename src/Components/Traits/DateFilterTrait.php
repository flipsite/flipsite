<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

trait DateFilterTrait
{
    use PathTrait;
    protected function parseDate(string $value, string $format = 'international'): string
    {
        $pattern = '/\b\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])\b/';
        preg_match_all($pattern, $value, $matches);
        if ($matches[0]) {
            $matches = array_unique($matches[0]);
            foreach ($matches as $match) {
                $value = str_replace($match, $this->convertToDateFormat($match, $format), $value);
            }
        }

        return $value;
    }
    protected function convertToDateFormat(string $date, string $format = 'full'): string
    {
        list($year, $month, $day) = explode('-', $date);
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            return $date;
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
                break;
            default:
                $pattern = $format;
                $format = \IntlDateFormatter::NONE;
        }
        $dateFormatter = new \IntlDateFormatter(
            (string)$this->path->getLanguage(),
            $format,
            \IntlDateFormatter::NONE,
            null,
            null,
            $pattern
        );
        return $dateFormatter->format($timestamp);
    }
}
