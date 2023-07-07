<?php

declare(strict_types=1);

namespace Flipsite\Components;

class Languages extends Nav
{
    public function normalize(string|int|bool|array $data): array
    {
        $active = $this->path->getLanguage();
        if (is_string($data) || is_string($data['items'])) {
            $items     = is_string($data) ? $data : $data['items'];
            $languages = explode(',', str_replace(' ', '', $items));
            $data      = ['items'=>[], 'options'=>$data['options'] ?? []];
            foreach ($languages as $lang) {
                $data['items'][] = [
                    'url'      => $lang,
                    'text'     => $lang,
                    'active'   => $active == $lang
                ];
            }
        }
        return $data;
    }

    public function build(array $data, array $style, array $options): void
    {
        // TODO add aria stuff for accessibility
        if (in_array('full', $data['flags']??[]) || ($data['options']['full'] ?? false)) {
            foreach ($data['items'] as &$item) {
                $item['text'] = $this->getString($item['url']);
            }
        }
        parent::build($data, $style, $options);
    }

    private function getString(string $languageCode): string
    {
        switch ($languageCode) {
            case 'en': return 'In English';
            case 'fi': return 'Suomeksi';
            case 'sv': return 'Svenska';
            default: return $languageCode;
        }
    }
}
