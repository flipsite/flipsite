<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

class Nav extends AbstractGroup
{
    use Traits\PathTrait;
    use Traits\RepeatTrait;
    use Traits\SlugsTrait;
    use Traits\ReaderTrait;

    protected string $tag = 'nav';

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_array($data) && !ArrayHelper::isAssociative($data)) {
            $data = ['items' => $data];
        }
        if (is_string($data)) {
            $data = ['items' => $data];
        }
        if (isset($data['items']) && is_string($data['items'])) {
            $data['items'] = $this->getFromSlugs($data['items']);
        }

        if (isset($data['repeat'],$data['item'])) {
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

        if ($data['options']['exact'] ?? false) {
            $data['items'][0]['exact'] = true;
        }

        $data['items'] = $this->addActive($data['items'], $this->path->getPage());

        if (isset($data['options'])) {
            $offset        = $data['options']['offset'] ?? 0;
            $length        = $data['options']['length'] ?? 999999;
            $data['items'] = array_slice($data['items'], $offset, $length);
        }

        return $data;
    }

    public function build(array $data, array $style, string $appearance) : void
    {
        $items = $data['items'] ?? [];
        unset($data['items']);
        $last = count($items) - 1;
        foreach ($items as $i => $item) {
            $item['style'] = ArrayHelper::merge($style['items'] ?? [], $item['style'] ?? []);
            if ($i === 0 && isset($style['first'])) {
                $item['style'] = ArrayHelper::merge($item['style'], $style['first']);
            } elseif ($i === $last && isset($style['last'])) {
                $item['style'] = ArrayHelper::merge($item['style'], $style['last']);
            }
            if (isset($item['active']) && $item['active'] && isset($style['active'])) {
                unset($item['active']);
                if (!isset($item['style'])) {
                    $item['style'] = [];
                }
                $item['style'] = ArrayHelper::merge($item['style'], $style['active']);
            }
            $data['a:'.$i] = $item;
        }
        unset($style['items'], $style['active'], $style['first'], $style['last']);

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

    private function getFromSlugs(string $page) : ?array
    {
        $pages      = [];
        $all        = $this->slugs->getPages();
        $firstExact = false;
        if ('pages' === $page || 'level-0' === $page) {
            $pages = array_filter($all, function ($value) {
                return mb_strpos((string)$value, '/') === false;
            });
        } elseif (str_starts_with($page, 'level-')) {
            $level           = intval(str_replace('level-', '', $page));
            $parts           = explode('/', $this->path->getPage());
            $startsWith      = implode('/', array_splice($parts, 0, $level));
            foreach ($all as $page) {
                $count = substr_count((string)$page, '/');
                if (str_starts_with((string)$page, $startsWith) && $count >= $level - 1 && $count <= $level) {
                    $pages[] = $page;
                }
            }
        } elseif (strpos($page, ',') !== false) {
            $pages = explode(',', str_replace(' ', '', $page));
        } else {
            $firstExact = true;
            $level      = substr_count((string)$page, '/');
            $pages      = array_filter($all, function ($value) use ($page, $level) {
                return str_starts_with((string)$value, (string)$page) && substr_count($value, '/') <= $level + 1;
            });
        }
        $items = [];
        foreach ($pages as $page) {
            $item            = [
                'url'  => $page,
                'text' => $this->reader->getPageName((string)$page, $this->path->getLanguage())
            ];
            if ($firstExact) {
                $item['exact'] = true;
                $firstExact    = false;
            }
            $items[] = $item;
        }
        return $items;
    }
}
