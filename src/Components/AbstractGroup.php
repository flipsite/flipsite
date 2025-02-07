<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Utils\Filter;
use Flipsite\Content\Collection;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

abstract class AbstractGroup extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\StyleOptimizerTrait;
    use Traits\ActionTrait;
    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    protected string $tag = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        if (isset($data['_action'])) {
            if ('tel' === $data['_action']) {
                // handle tel replace
            }
            if ('mailto' === $data['_action']) {
                // handle tel replace
            }
            if ('scrollLeft' === $data['_action'] || 'scrollRight' === $data['_action']) {
                $this->builder->dispatch(new Event('global-script', 'scrollX', file_get_contents(__DIR__ . '/../../js/dist/scrollX.min.js')));
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

        $repeatData = $data['_repeatData'] ?? false;
        if ($repeatData) {
            $children        = [];
            $total           = count($repeatData);
            $index           = 0;

            foreach ($repeatData as $repeatDataItem) {
                foreach ($component->getChildren() as $childComponent) {
                    $clonedChildComponent = clone $childComponent;
                    $clonedChildComponent->setDataValue('_dataSource', $repeatDataItem);
                    $clonedChildComponent->setDataValue('_repeatIndex', $repeatDataItem['index']);
                    $optimizedStyle = $this->optimizeStyle($clonedChildComponent->getStyle(), $index, $total);

                    if (isset($optimizedStyle['background'])) {
                        $optimizedStyle['background'] = $this->optimizeStyle($optimizedStyle['background'], $index, $total);
                    }
                    $clonedChildComponent->setStyle($optimizedStyle);
                    $children[] = $this->builder->build($clonedChildComponent, clone $inherited);
                }
            }
            $this->addChildren($children);
        } else {
            $children        = [];
            $index           = 0;
            $childComponents = $component->getChildren();
            $total           = count($childComponents);

            foreach ($childComponents as $childComponent) {
                $componentStyle = $this->optimizeStyle($childComponent->getStyle(), $index, $total);
                $childComponent->setStyle($componentStyle);
                $children[]     = $this->builder->build($childComponent, $inherited);
                $index++;
            }

            $this->addChildren($children);
        }
    }

    public function normalize(array $data): array
    {
        $data = $this->normalizeAction($data);
        $data = $this->normalizeHover($data);

        $repeatCollectionName = null;
        if (isset($data['_repeat'])) {
            $repeat = $data['_repeat'];
            unset($data['_repeat']);
            if (is_string($repeat)) {
                $repeatCollectionId = $repeat;
                $collection         = $this->getCollection($repeat, true);
                if ($collection) {
                    $repeat               = $collection->getItemsArray(true);
                    $repeatCollectionName = $collection->getName();
                } else {
                    $repeat = [];
                }
            }
            $data = $this->normalizeRepeat($data, $repeat);
            if (isset($data['_attr']['id'])) {
                $id = $data['_attr']['id'];
                $this->builder->shareData($id, $data['_repeatData'] ?? []);
            }
        }
        if ($repeatCollectionName) {
            $data['_repeatCollectionName'] = $repeatCollectionName;
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
        // Unclear if this is used anymore
        if (isset($data['_options']['filter']) && is_string($data['_options']['filter']) && strpos($data['_options']['filter'], 'this.slug')) {
            // Do something
        }

        if (isset($data['_options']['filterField']) && (isset($data['_options']['filter']) || isset($data['_options']['filterType']) || isset($data['_options']['filterPattern']))) {
            $field = $data['_options']['filterField'];
            if (strpos($field, '|') !== false) {
                $parts = explode('|', $field);
                $field = $parts[1];
            }
            $filter = new Filter($data['_options']['filterType'] ?? 'or', $data['_options']['filter'] ?? null, $data['_options']['filterPattern'] ?? null);
            $repeat = $filter->filterList($repeat, $field);
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

        if (isset($data['_options']['offset']) || isset($data['_options']['length'])) {
            $offset = intval($data['_options']['offset'] ?? 0);
            $length = intval($data['_options']['length'] ?? 999999);
            $repeat = array_splice($repeat, $offset, $length);
        }

        $components = array_filter($data, function ($key): bool {
            return !str_starts_with($key, '_');
        }, ARRAY_FILTER_USE_KEY);
        foreach (array_keys($components) as $key) {
            unset($data[$key]);
        }
        $data['_repeatData'] = $repeat ?? [];

        if (!is_array($repeat) || !count($repeat)) {
            unset($data['_repeatData']);

            $data['_isEmpty'] = true;
        } else {
            $index0 = 0;
            foreach ($data['_repeatData'] as &$item) {
                $item['index'] = ++$index0;
            }
        }

        if ($data['_options']['duplicate'] ?? false) {
            $count            = intval($data['_options']['duplicate']) ?? 1;
            $duplicated       = [];
            for ($i = 0; $i <= $count; $i++) {
                $duplicated = array_merge($duplicated, $data['_repeatData']);
            }
            $data['_repeatData'] = $duplicated;
        }

        return $data;
    }

    private function getCollection(string $collectionId): ?Collection
    {
        // Old YAML style reference
        if (str_starts_with($collectionId, '${content.')) {
            $collectionId = substr($collectionId, 10, strlen($collectionId) - 11);
        }
        $collection = $this->siteData->getCollection($collectionId, $this->path->getLanguage());
        if (!$collection) {
            return null;
        }
        return $collection;
    }
}
