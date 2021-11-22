<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Breadcrumb extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;
    use Traits\ReaderTrait;

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
                    'text' => $this->reader->getPageName('home', $this->path->getLanguage())
                ]];
                unset($data['home']);
            } else {
                $data['items']   = [];
                $path            = explode('/', $page);
                while (count($path) > 0) {
                    $page            = implode('/', $path);
                    $data['items'][] = [
                        'url'  => $page,
                        'text' => $this->reader->getPageName($page, $this->path->getLanguage())
                    ];
                    array_pop($path);
                }
                $data['items'][] = $data['home'] ?? [
                    'url'  => 'home',
                    'text' => $this->reader->getPageName('home', $this->path->getLanguage())
                ];
                unset($data['home']);
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

        $separatorType = $data['separator']['type'] ?? 'span';
        $separatorData = $data['separator'] ?? ['value' => '/'];

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
                $data[$separatorType.':'.$i] = $separatorData;
            }
        }
        unset($style['items'], $style['active'], $data['separator']);

        parent::build($data, $style, $appearance);
    }
}
