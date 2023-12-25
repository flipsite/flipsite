<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Social extends AbstractGroup
{
    public function normalize(string|int|bool|array $data): array
    {
        unset($data['dataSourceList']);
        $dataSourceList = $this->getSocial();
        $data = $this->normalizeRepeat($data, $dataSourceList);
        return $data;
    }
    private function getSocial(): array
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
            $items[]        = $item;
        }
        return $items;
    }
}
