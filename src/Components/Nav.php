<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Nav extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    protected string $type = 'nav';

    public function build(array $data, array $style, array $flags) : void
    {
        $items = $this->addIsActive($data, $this->path->getPage());
        $this->addStyle($style['container'] ?? []);
        unset($style['container']);
        foreach ($items as $item) {
            $isActive = $item['isActive'];
            unset($item['isActive']);
            $a = $this->builder->build('a', $item, ['a' => $style]);
            if ($isActive && isset($style['active'])) {
                $a->addStyle($style['active']);
            }
            $this->addChild($a);
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
                $item['isActive'] = $item['isActive'] ?? false;
                $item['exact']    = $item['exact']    ?? false;
                if ($item['exact'] && $item['url'] === $active) {
                    $item['isActive'] = true;
                } elseif (!$item['exact']) {
                    $item['isActive'] = $item['url'] === mb_substr($active, 0, mb_strlen($item['url']));
                }
            }
            unset($item['exact']);
        }
        return $items;
    }
}
