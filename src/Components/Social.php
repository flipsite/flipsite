<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Social extends AbstractGroup
{
    public function normalize(array $data): array
    {
        $dataSourceList = $this->getSocial();
        $dataSourceList = $this->applyCustom($dataSourceList, $data);

        $filter     = ArrayHelper::decodeJsonOrCsv($data['filter'] ?? null);
        $filterType = $data['filterType'] ?? 'or';
        $sort       = ArrayHelper::decodeJsonOrCsv($data['sort'] ?? null);

        unset($data['filter'],$data['filterType'],$data['phoneValue'], $data['phoneIcon'],$data['emailIcon'],$data['mapsIcon'],$data['openIcon']);

        $data = $this->normalizeRepeat($data, $dataSourceList);
        if ($filter) {
            $repeatData = [];
            foreach ($data['_repeatData'] ?? [] as $item) {
                $repeatData[$item['type']] = $item;
            }
            $data['_repeatData'] = [];
            if ('or' === $filterType) {
                foreach ($filter as $type) {
                    if (isset($repeatData[$type])) {
                        $data['_repeatData'][] = $repeatData[$type];
                    }
                }
            } elseif ('not' === $filterType) {
                foreach ($repeatData as $type => $item) {
                    if (!in_array($type, $filter)) {
                        $data['_repeatData'][] = $item;
                    }
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
}
