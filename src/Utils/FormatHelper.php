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

    public static function convertToPhoneFormat(string $number, string $format = 'international'): string
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $phoneNumber = $phoneUtil->parse($number, '');
        } catch (\libphonenumber\NumberParseException $e) {
            return $number;
        }
        switch ($format) {
            case 'e164':
                $formatType = \libphonenumber\PhoneNumberFormat::E164;
                break;
            case 'rfc3966':
                $formatType = \libphonenumber\PhoneNumberFormat::RFC3966;
                break;
            case 'national':
                $formatType = \libphonenumber\PhoneNumberFormat::NATIONAL;
                break;
            case 'international':
            default:
                $formatType = \libphonenumber\PhoneNumberFormat::INTERNATIONAL;
        }
        return $phoneUtil->format($phoneNumber, $formatType);
    }
}
