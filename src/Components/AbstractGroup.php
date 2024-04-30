<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Utils\ArrayHelper;

abstract class AbstractGroup extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\StyleOptimizerTrait;
    use Traits\ActionTrait;
    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, array $options): void
    {
        $this->tag ??= $style['tag'];
        unset($style['tag']);

        if (isset($data['_action'])) {
            if ('tel' === $data['_action']) {
                // handle tel replace
            }
            if ('mailto' === $data['_action']) {
                // handle tel replace
            }
            if ('toggle' === $data['_action']) {
                $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__ . '/../../js/toggle.min.js')));
            }
            if ('scrollLeft' === $data['_action'] || 'scrollRight' === $data['_action']) {
                $this->builder->dispatch(new Event('global-script', 'scrollX', file_get_contents(__DIR__ . '/../../js/scrollX.min.js')));
            }
            $actionAttributes = $this->getActionAttributes($data);
            if (isset($actionAttributes['tag'])) {
                $this->tag = $actionAttributes['tag'];
                unset($actionAttributes['tag']);
            }
            foreach ($actionAttributes as $attr => $val) {
                $this->setAttribute($attr, $val);
            }
        }

        $this->addStyle($style);

        $repeatTpl   = $data['_repeatTpl'] ?? false;
        $repeatData  = $data['_repeatData'] ?? false;
        if ($repeatTpl) {
            unset($data['_repeatTpl'], $data['_repeatData']);
            $children = [];
            $total    = count($repeatData);

            foreach ($repeatData as $i => $repeatDataItem) {
                foreach ($repeatTpl as $type => $repeatTplComponent) {
                    if (!is_array($repeatTplComponent)) {
                        $repeatTplComponent = ['value' => $repeatTplComponent];
                    }
                    $repeatTplComponent['_dataSource']       = $repeatDataItem;
                    $repeatTplComponent['_repeatIndex']      = $repeatDataItem['index'];
                    $optimizedStyle                          = $this->optimizeStyle($style[$type] ?? [], $i, $total);
                    if (isset($optimizedStyle['background'])) {
                        $optimizedStyle['background'] = $this->optimizeStyle($optimizedStyle['background'], $i, $total);
                    }
                    $children[] = $this->builder->build($type, $repeatTplComponent, $optimizedStyle, $options);
                }
            }
            $this->addChildren($children);
        } else {
            $children = [];
            $i        = 0;
            $total    = count($data);
            foreach ($data as $type => $componentData) {
                $componentStyle = $this->optimizeStyle($style[$type] ?? [], $i, $total);
                $children[]     = $this->builder->build($type, $componentData ?? [], $componentStyle, $options);
                $i++;
            }

            $this->addChildren($children);
        }
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        $data = $this->normalizeAction($data);
        $data = $this->normalizeHover($data);

        if (isset($data['_repeat'])) {
            $repeat = $data['_repeat'];
            unset($data['_repeat']);
            if (is_string($repeat)) {
                $repeat = $this->getCollection($repeat, true);
            }
            $data = $this->normalizeRepeat($data, $repeat);
        }
        return $data;
    }

    private function normalizeAction(string|int|bool|array $data): array
    {
        if (isset($data['_page'])) {
            $data['_action'] = 'page';
            $data['_target'] = $data['_page'];
            unset($data['_page']);
        }
        if (isset($data['_params'])) {
            $expanded        = $this->siteData->getExpanded($data['_target']);
            $data['_target'] = str_replace(':slug', $data['_params'], $expanded[(string)$this->path->getLanguage()] ?? $data['_target']);
            unset($data['_params']);
        }
        return $data;
    }

    private function normalizeHover(string|int|bool|array $data): array
    {
        if (!isset($data['_hover'])) {
            return $data;
        }
        $parts = explode('|', $data['_hover']);
        switch ($parts[0]) {
            case 'toggle':
                $width = $parts[1] ?? 768;
                $this->setAttribute('onmouseenter', 'javascript:toggle(this,true,'.$width.')');
                $this->setAttribute('onmouseleave', 'javascript:toggle(this,false,'.$width.')');
                $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__ . '/../../js/toggle.min.js')));
                break;
        }
        unset($data['_hover']);
        return $data;
    }

    protected function normalizeRepeat(string|int|bool|array $data, array $repeat): array
    {
        // TODO remove at some point, backwards compability
        if (isset($data['_options']['filterBy']) && !isset($data['_options']['filterField'])) {
            $data['_options']['filterField'] = $data['_options']['filterBy'];
            unset($data['_options']['filterBy']);
        }
        if (isset($data['_options']['filterField'])) {
            $filter = null;
            if ('true' === ($data['_options']['filter'] ?? '')) {
                $filter = true;
            } elseif ('false' === ($data['_options']['filter'] ?? '')) {
                $filter = false;
            } else {
                $filter = ArrayHelper::decodeJsonOrCsv($data['_options']['filter'] ?? '');
            }
            foreach ($filter as &$f) {
                if ('{this.slug}' === $f) {
                    $f = $this->path->getPage();
                }
            }
            $filterField = $data['_options']['filterField'];
            $filterType  = $data['_options']['filterType'] ?? 'or'; // Can be or, not or notEmpty
            $repeat      = array_values(array_filter($repeat, function ($item) use ($filter, $filterField, $filterType) {
                if ('notEmpty' === $filterType) {
                    return isset($item[$filterField]) && $item[$filterField];
                }
                if (is_bool($filter)) {
                    $value = $item[$filterField] ?? false;
                    return $filter === $value;
                }
                if (!isset($item[$filterField])) {
                    return 'or' !== $filterType;
                }

                if ($item) {
                    $fieldFieldValues = ArrayHelper::decodeJsonOrCsv($item[$filterField]);
                }
                $count = count(array_intersect($fieldFieldValues, $filter));
                return 'or' === $filterType ? $count > 0 : $count === 0;
            }));
        }
        if (isset($data['_options']['filterField'], $data['_options']['filterPattern'])) {
            $filterField       = $data['_options']['filterField'];
            $filterPattern     = $data['_options']['filterPattern'];
            $repeat            = array_values(array_filter($repeat, function ($item) use ($filterPattern, $filterField) {
                return preg_match('/'.$filterPattern.'/', $item[$filterField]);
            }));
        }

        if (isset($data['_options']['offset']) || isset($data['_options']['length'])) {
            $offset = intval($data['_options']['offset'] ?? 0);
            $length = intval($data['_options']['length'] ?? 999999);
            $repeat = array_splice($repeat, $offset, $length);
        }

        if (isset($data['_options']['sortBy'])) {
            $sortField = $data['_options']['sortBy'];
            uasort($repeat, function ($a, $b) use ($sortField) {
                if (isset($a[$sortField],$b[$sortField])) {
                    return $a[$sortField] <=> $b[$sortField];
                }
                return 0;
            });
        }
        if (isset($data['_options']['sort']) && 'desc' === $data['_options']['sort']) {
            $repeat = array_reverse($repeat);
        }

        $components = array_filter($data, function ($key): bool {
            return !str_starts_with($key, '_');
        }, ARRAY_FILTER_USE_KEY);
        foreach (array_keys($components) as $key) {
            unset($data[$key]);
        }
        $data['_repeatTpl']  = $components;
        $data['_repeatData'] = $repeat ?? [];

        if (!is_array($repeat) || !count($repeat)) {
            unset($data['_repeatTpl'], $data['_repeatData']);

            $data['_isEmpty'] = true;
        } else {
            $index0 = 0;
            foreach ($data['_repeatData'] as &$item) {
                $item['index'] = ++$index0;
            }
        }

        return $data;
    }

    private function getCollection(string $collectionId): array
    {
        // Old YAML style reference
        if (str_starts_with($collectionId, '${content.')) {
            $collectionId = substr($collectionId, 10, strlen($collectionId) - 11);
        }
        $collection = $this->siteData->getCollection($collectionId, $this->path->getLanguage());
        if (!$collection) {
            return [];
        }
        return $collection->getItemsArray(true);
    }
}
