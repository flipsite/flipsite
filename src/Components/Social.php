<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Social extends AbstractGroup
{
    public function normalize(string|int|bool|array $data): array
    {
        $dataSourceList = $this->getSocial($data['phoneFormat'] ?? null);
        unset($data['phoneFormat']);
        $data = $this->normalizeRepeat($data, $dataSourceList);
        return $data;
    }
    private function getSocial(?string $phoneFormat = null): array
    {
        $name     = $this->siteData->getName();
        $language = $this->path->getLanguage();
        $items    = [];
        $i        = 0;
        $social   = $this->siteData->getSocial();
        if (!$social) {
            return [];
        }
        
        foreach ($social as $type => $handle) {
            $item           = \Flipsite\Utils\SocialHelper::getData($type, (string)$handle, $name, $language);
            $item['handle'] = $handle;
            $item['url']    = $item['url'];
            if ($phoneFormat && 'phone' === $type) {
                $item['name'] = $this->getFormattedPhoneNumber($handle, $phoneFormat);
            }
            $items[] = $item;
        }


        return $items;
    }
    private function getFormattedPhoneNumber(string $value, string $format) : string {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $phoneNumber = $phoneUtil->parse($value, '');
        } catch (\libphonenumber\NumberParseException $e) {
            return $value;
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

        return  $phoneUtil->format($phoneNumber, $format);
    }
}
