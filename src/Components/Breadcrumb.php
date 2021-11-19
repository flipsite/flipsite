<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Breadcrumb extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    protected string $tag = 'nav';

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            $data = ['items' => $data];
        }
        if (!isset($data['items']) || is_bool($data['items'])) {
            $data['items'] = 'self';
        }

        if (is_string($data['items'])) {
            $page = 'self' === $data['items'] ? $this->path->getPage() : $data['items'];
            if ('home' === $page) {
                $data['items'] = [$data['home'] ?? [
                    'url'  => 'home',
                    'text' => 'Hem'
                ]];
                unset($data['home']);
                return $data;
            } else {
                $data['items']   = [];
                $path            = explode('/', $page);
                while (count($path) > 0) {
                    $page            = implode('/', $path);
                    $tmp             = explode('/', $page);
                    $data['items'][] = [
                        'url'  => $page,
                        'text' => array_pop($tmp)
                    ];
                    array_pop($path);
                }
                $data['items'][] = $data['home'] ?? [
                    'url'  => 'home',
                    'text' => 'Hem'
                ];
                $data['items'] = array_reverse($data['items']);
            }
        }
        $data['items'][array_key_last($data['items'])]['active'] = true;
        return $data;
    }

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->addStyle($style);
        $items = $data['items'] ?? [];
        unset($data['items']);

        foreach ($items as $i => $item) {
            $item['style'] = ArrayHelper::merge($style['items'] ?? [], $item['style'] ?? []);
            if (isset($item['active']) && $item['active'] && isset($style['active'])) {
                unset($item['active']);
                if (!isset($item['style'])) {
                    $item['style'] = [];
                }
                $item['style'] = ArrayHelper::merge($item['style'], $style['active'] ?? null);
            }
            $data['a:'.$i] = $item;
            if ($i !== count($items) - 1) {
                $data['span:'.$i] = '/';
            }
        }
        unset($style['items'], $style['active']);

        parent::build($data, $style, $appearance);
    }
}

        // $this->addStyle($data->getStyle('container'));
        // $this->setAttribute('aria-label', 'breadcrumb');
        // $separator = $data->get('separator', true) ?? '/';
        // $keys = array_keys($data->get());
        // $keys = array_reverse($keys);
        // $last = $keys[0];
        // foreach ($data->get() as $url => $item) {
        //     if (is_string($item)) {
        //         $item = [
        //             'text' => $item,
        //             'url'  => $url,
        //         ];
        //     }
        //     $components = $this->builder->build(['a' => $item], ['a' => $data->getStyle()], $data->getAppearance());
        //     $a = $components[0];
        //     if ($url === $last) {
        //         $a->addStyle($data->getStyle('current'));
        //         $a->setAttribute('aria-current', 'page');
        //         $this->addChild($a);
        //     } else {
        //         $this->addChild($a);
        //         $span = new Element('span', true);
        //         $span->addStyle($data->getStyle('separator'));
        //         $span->setContent($separator);
        //         $this->addChild($span);
        //     }
        // }
