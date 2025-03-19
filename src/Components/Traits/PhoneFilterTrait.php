<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

trait PhoneFilterTrait
{
    protected function parsePhone(string $value, string $format = 'international'): string
    {
        if ('international' === $format) {
            return $value;
        }
        $pattern = '/\+\d{7,15}/';
        preg_match_all($pattern, $value, $matches);

        if ($matches[0]) {
            $matches = array_unique($matches[0]);
            foreach ($matches as $match) {
                $value = str_replace($match, $this->convertToPhoneFormat($match, $format), $value);
            }
        }

        return $value;
    }
    protected function convertToPhoneFormat(string $number, string $format): string
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $phoneNumber = $phoneUtil->parse($number, '');
        } catch (\libphonenumber\NumberParseException $e) {
            return $number;
        }
        switch ($format) {
            case 'e164':
                $format = \libphonenumber\PhoneNumberFormat::E164;
                break;
            case 'rfc3966':
                $format = \libphonenumber\PhoneNumberFormat::RFC3966;
                break;
            case 'national':
                $format = \libphonenumber\PhoneNumberFormat::NATIONAL;
                break;
            case 'international':
            default:
                $format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL;
        }
        return $phoneUtil->format($phoneNumber, $format);
    }
}
