<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Social extends AbstractGroup
{
    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        $dataSourceList = $this->getSocial();
        $dataSourceList = $this->applyCustom($dataSourceList, $data);

        $filter     = ArrayHelper::decodeJsonOrCsv($data['filter'] ?? null);
        $sort       = ArrayHelper::decodeJsonOrCsv($data['sort'] ?? null);

        unset($data['filter'],$data['phoneFormat'],$data['phoneValue'], $data['phoneIcon'],$data['emailIcon'],$data['mapsIcon'],$data['openIcon']);

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
            $item                = \Flipsite\Utils\SocialHelper::getData($type, (string)$handle, $name, $language);
            $item['_id']         = $type;
            $item['_collection'] = '_social';
            $item['type']        = $type;
            $item['handle']      = $handle;
            $item['url']         = $item['url'];
            $item['color']       = '['.$item['color'].']';
            $items[]             = $item;
        }
        return $items;
    }

    private function applyCustom(array $social, array $data) : array
    {
        foreach ($social as &$item) {
            switch ($item['type']) {
                case 'phone':
                    $item['icon'] = $data['phoneIcon'] ?? $item['icon'];
                    $item['name'] = $data['phoneValue'] ?? $item['name'];
                    if (!isset($data['phoneValue']) && isset($data['phoneFormat'])) {
                        $item['name'] = $this->getFormattedPhoneNumber($item['handle'], $data['phoneFormat']);
                    }
                    break;
                case 'email':
                    $item['icon'] = $data['emailIcon'] ?? $item['icon'];
                    break;
                case 'maps':
                    $item['icon'] = $data['mapsIcon'] ?? $item['icon'];
                    $item['name'] = $data['mapsValue'] ?? $item['name'];
                    break;
                case 'open':
                    $item['icon'] = $data['openIcon'] ?? $item['icon'];
                    $item['name'] = $item['handle'] ?? $item['name'];
                    break;
            }
        }
        return $social;
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
