<?php

declare(strict_types=1);
namespace Flipsite\Components;

class Languages extends Nav
{
    public function normalize(string|int|bool|array $data) : array
    {
        $active = $this->path->getLanguage();
        if (is_string($data)) {
            $languages = explode(',', $data);
            $data      = ['items'=>[]];
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

    public function build(array $data, array $style, string $appearance) : void
    {
        // TODO add aria stuff for accessibility
        if (in_array('full', $data['flags'])) {
            foreach ($data['items'] as &$item) {
                $item['text'] = $this->getString($item['url']);
            }
        }
        parent::build($data, $style, $appearance);
    }

    private function getString(string $languageCode) : string
    {
        switch ($languageCode) {
            case 'en': return 'English';
            case 'fi': return 'Suomeksi';
            case 'sv': return 'Svenska';
            default: return $languageCode;
        }
    }
}
