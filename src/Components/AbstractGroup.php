<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;

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
                    $repeatTplComponent['_dataSource'] = $repeatDataItem;
                    $optimizedStyle                    = $this->optimizeStyle($style[$type] ?? [], $i, $total);
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
            $data['_target'] = str_replace(':slug', $data['_params'], $data['_target']);
            unset($data['_params']);
        }
        if (isset($data['_params'])) {
            $data['_target'] = str_replace(':slug', $data['_params'], $data['_target']);
            unset($data['_params']);
        }
        return $data;
    }

    private function normalizeHover(string|int|bool|array $data): array
    {
        if (!isset($data['_hover'])) {
            return $data;
        }
        switch ($data['_hover']) {
            case 'toggle':
                $this->setAttribute('onmouseenter', 'javascript:toggle(this,true,768)');
                $this->setAttribute('onmouseleave', 'javascript:toggle(this,false,768)');
                $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__ . '/../../js/toggle.min.js')));
                break;
        }
        unset($data['_hover']);
        return $data;
    }

    protected function normalizeRepeat(string|int|bool|array $data, array $repeat): array
    {
        if (isset($data['_options']['filter'], $data['_options']['filterField'])) {
            $filter = json_decode($data['_options']['filter'], true);
            if (null === $filter && $data['_options']['filter']) {
                $filter = explode(',', $data['_options']['filter']);
            }
            $filter      = array_map('trim', $filter ?? []);
            $filterField = $data['_options']['filterField'];

            $repeat = array_values(array_filter($repeat, function ($item) use ($filter, $filterField) {
                if (!isset($item[$filterField])) {
                    return false;
                }
                $fieldFieldValues = json_decode($item[$filterField]);
                if (null === $fieldFieldValues && $item[$filterField]) {
                    $fieldFieldValues = explode(',', $item[$filterField]);
                }
                $fieldFieldValues = array_map('trim', $fieldFieldValues);
                return count(array_intersect($fieldFieldValues, $filter)) > 0;
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
            foreach ($data['_repeatData'] as $index0 => &$item) {
                $item['index'] = $index0 + 1;
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
        $collection = $this->siteData->getCollection($collectionId);
        if (!$collection) {
            return [];
        }
        return $collection->getContent(true);
    }
}
