<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Nav extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    protected string $tag = 'nav';

    public function with(ComponentData $data) : void
    {
        $items = $this->normalize($data->get());
        $flags = $data->getFlags();
        if (in_array('onpage', $flags)) {
            $items[0]['isActive'] = true;
            foreach ($items as &$item) {
                $item['url'] = '#'.$item['url'];
                $this->setAttribute('data-nav', true);
                $activeStyle = ArrayHelper::merge($data->getStyle(), $data->getStyle('active'));
                $activeStyle = array_filter($activeStyle, function ($item) {
                    return is_string($item);
                });
                sort($activeStyle);
                $this->setAttribute('data-active', implode(' ', $activeStyle));
                $this->builder->dispatch(new Event('ready-script', 'nav', file_get_contents(__DIR__.'/../../js/nav.js')));
            }
        } else {
            $items = $this->addIsActive($items, $this->path->getPage());
        }
        $this->addStyle($data->getStyle('container'));
        foreach ($items as &$item) {
            if ($item['isActive'] ?? false) {
                $item['style'] = $data->getStyle('active');
            }
            unset($item['isActive']);
            $components = $this->builder->build(['a' => $item], ['a' => $data->getStyle()], $data->getAppearance());
            $this->addChildren($components);
        }
    }

    protected function normalize($data) : array
    {
        if (ArrayHelper::isAssociative($data)) {
            $items = [];
            foreach ($data as $url => $value) {
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
                $items[] = $item;
            }
        } else {
            return $data;
        }
        return $items;
    }

    private function addIsActive(array $items, string $active) : array
    {
        foreach ($items as &$item) {
            // If URL is an array, it's a localized external URL that cannot be active
            if (isset($item['url']) && is_string($item['url'])) {
                $url              = explode('#', $item['url'])[0];
                $item['isActive'] = $item['isActive'] ?? false;
                $item['exact']    = $item['exact'] ?? false;
                if ($item['exact'] && $url === $active) {
                    $item['isActive'] = true;
                } elseif (!$item['exact']) {
                    $item['isActive'] = $url === mb_substr($active, 0, mb_strlen($url));
                }
            }
            unset($item['exact']);
        }
        return $items;
    }
}
