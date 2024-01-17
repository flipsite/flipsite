<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Social extends AbstractGroup
{
    public function normalize(string|int|bool|array $data): array
    {
        $dataSourceList = $this->getSocial($data['phoneFormat'] ?? null);
        unset($data['phoneFormat']);
        $filter     = isset($data['filter']) ? json_decode($data['filter'], true) : false;
        $sort       = isset($data['sort']) ? json_decode($data['sort'], true) : false;
        $phoneIcon  = $data['phoneIcon'] ?? false;
        $emailIcon  = $data['emailIcon'] ?? false;
        unset($data['filter'],$data['phoneIcon'],$data['emailIcon']);

        $data = $this->normalizeRepeat($data, $dataSourceList);
        if ($filter) {
            $repeatData = [];
            foreach ($data['_repeatData'] as $item) {
                $repeatData[$item['type']] = $item;
            }
            $data['_repeatData'] = [];
            foreach ($filter as $type) {
                if (isset($repeatData[$type])) {
                    $data['_repeatData'][] = $repeatData[$type];
                }
            }
        }
        if ($sort) {
            $repeatData = [];
            foreach ($data['_repeatData'] as $item) {
                $repeatData[$item['type']] = $item;
            }
            $data['_repeatData'] = [];
            foreach ($sort as $type) {
                if (isset($repeatData[$type])) {
                    $data['_repeatData'][] = $repeatData[$type];
                    unset($repeatData[$type]);
                }
            }
            $data['_repeatData'] = array_values(array_merge($data['_repeatData'], $repeatData));
        }

        if ($filter || $sort) {
            foreach ($data['_repeatData'] as $index => &$item) {
                $item['index'] = $index + 1;
            }
        }

        if ($phoneIcon) {
            foreach ($data['_repeatData'] as &$item) {
                if ('phone' === $item['type']) {
                    $item['icon'] = $phoneIcon;
                }
            }
        }
        if ($emailIcon) {
            foreach ($data['_repeatData'] as &$item) {
                if ('email' === $item['type']) {
                    $item['icon'] = $emailIcon;
                }
            }
        }
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
            $item['type']   = $type;
            $item['handle'] = $handle;
            $item['url']    = $item['url'];
            if ($phoneFormat && 'phone' === $type) {
                $item['name'] = $this->getFormattedPhoneNumber($handle, $phoneFormat);
            }
            $items[] = $item;
        }

        return $items;
    }

    private function getFormattedPhoneNumber(string $value, string $format) : string
    {
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
