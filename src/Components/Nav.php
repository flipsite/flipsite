<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

class Nav extends AbstractGroup
{
    use Traits\PathTrait;
    use Traits\RepeatTrait;

    protected string $tag = 'nav';

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            throw new \Exception('Nav data not array');
        }
        if (!ArrayHelper::isAssociative($data)) {
            $data = ['items' => $data];
        }

        if (is_array($data) && isset($data['repeat'],$data['item'])) {
            // TODO maybe import file content here
            $data['items'] = $this->expandRepeat($data['repeat'], $data['item']);
            unset($data['repeat'], $data['item']);
        }

        if (ArrayHelper::isAssociative($data) && !isset($data['items'])) {
            $style = $data['style'] ?? [];
            unset($data['style']);
            $data = ['items' => $data, 'style'=>$style];
        }

        if (ArrayHelper::isAssociative($data['items'])) {
            $items = [];
            foreach ($data['items'] as $url => $value) {
                $args = explode('|', $url);
                $url  = array_shift($args);
                if (is_string($value)) {
                    $item = [
                        'url'  => $url,
                        'text' => $value,
                    ];
                } else {
                    if (!isset($value['text'])) {
                        $item = ['text' => $value];
                    } else {
                        $item = $value;
                    }
                    if (!isset($item['url'])) {
                        $item['url'] = $url;
                    }
                }
                // Inline options, e.g. |exact
                foreach ($args as $attr) {
                    $item[$attr] = true;
                }
                $items[]       = $item;
                $data['items'] = $items;
            }
        }

        $data['items'] = $this->addActive($data['items'], $this->path->getPage());

        return $data;
    }

    public function build(array $data, array $style, string $appearance) : void
    {
        $items = $data['items'] ?? [];
        unset($data['items']);
        foreach ($items as $i => $item) {
            $item['style'] = ArrayHelper::merge($style['items'] ?? [], $item['style'] ?? []);
            if ($item['active'] && isset($style['active'])) {
                unset($item['active']);
                if (!isset($item['style'])) {
                    $item['style'] = [];
                }
                $item['style'] = ArrayHelper::merge($item['style'], $style['active']);
            }
            $data['a:'.$i] = $item;
        }
        unset($style['items'], $style['active']);

        parent::build($data, $style, $appearance);
    }

    private function addActive(array $items, string $active) : array
    {
        $activeParts = explode('/', $active);
        foreach ($items as &$item) {
            // If URL is an array, it's a localized external URL that cannot be active
            if (isset($item['url']) && is_string($item['url'])) {
                $url              = explode('#', $item['url'])[0];
                $item['active']   = $item['active'] ?? false;
                $item['exact']    = $item['exact'] ?? false;
                if ($item['exact'] && $url === $active) {
                    $item['active'] = true;
                } elseif (!$item['exact']) {
                    $urlParts       = explode('/', $url);
                    $item['active'] = $this->compare($urlParts, $activeParts);
                }
            }
            unset($item['exact']);
        }
        return $items;
    }

    private function compare(array $url, array $active): bool
    {
        $same = 0;
        foreach ($url as $i => $a) {
            if ($a === ($active[$i] ?? '')) {
                $same++;
            }
        }
        return $same >= count($url);
    }
}
