<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Phone extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected string $tag  = 'span';

    public function build(array $data, array $style, array $options) : void
    {
        $this->addStyle($style);
        $value     = $data['value'] ?? '+358501234567';
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $phoneNumber = $phoneUtil->parse($value, '');
        } catch (\libphonenumber\NumberParseException $e) {
            $this->render = false;
            return;
        }
        switch ($data['format'] ?? 'national') {
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

        $number = $phoneUtil->format($phoneNumber, $format);
        if (isset($data['content'])) {
            $string = str_replace('[number]', $number, $data['content']);
        } else {
            $string = $number;
        }

        if ($data['flag'] ?? false) {
            // Get the country code
            $countryCode = $phoneNumber->getCountryCode();
            $regionCode  = $phoneUtil->getRegionCodeForNumber($phoneNumber);
            $country     = strtolower($regionCode);
            $flag        = $this->builder->build('svg', ['value' => 'flag-icons-4x3/'.$country.'.svg'], $style['flag'] ?? [], $options);
            $this->addChild($flag);
            $text = $this->builder->build('text', ['value' => $number], [], $options);
            $this->addChild($text);
        } else {
            $this->setContent($number);
        }
    }
}
