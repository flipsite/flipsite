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
    use Traits\EnvironmentTrait;
    use Traits\BuilderTrait;
    use Traits\ActionTrait;
    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    protected string $tag = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        if (isset($data['_action'])) {
            if ('scrollX' === $data['_action'] || 'scrollLeft' === $data['_action'] || 'scrollRight' === $data['_action']) {
                $this->builder->dispatch(new Event('global-script', 'scrollX', file_get_contents(__DIR__ . '/../../js/dist/scrollX.min.js')));
            }
            $actionAttributes = $this->getActionAttributes($data);
            if (isset($actionAttributes['tag'])) {
                $this->tag = $actionAttributes['tag'];
                unset($actionAttributes['tag']);
            }
            if ($inherited->hasATag()) {
                $this->tag = 'span';
                if (isset($actionAttributes['href'])) {
                    $this->setAttribute('data-href', $actionAttributes['href']);
                    unset($actionAttributes['href']);
                }
            }
            // A tags can't be nested
            if ($this->tag === 'a') {
                $inherited->setATag(true);
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
            $addedNoRepeat   = [];
            foreach ($repeatData as $repeatDataItem) {
                $collectionId = $repeatDataItem['_collectionId'] ?? null;
                $itemId       = $repeatDataItem['_id'] ?? null;
                unset($repeatDataItem['_collectionId'],$repeatDataItem['_id']);
                foreach ($component->getChildren() as $childComponent) {
                    $data            = $childComponent->getData();
                    $clonedInherited = clone $inherited;
                    $clonedInherited->setRepeatComponentId($component->getId());
                    if (isset($data['_options']['repeatable']) && false === $data['_options']['repeatable']) {
                        if (!in_array($childComponent->getId(), $addedNoRepeat)) {
                            $addedNoRepeat[] = $childComponent->getId();
                            $children[]      = $this->builder->build($childComponent, $clonedInherited);
                        }
                        continue 1;
                    }
                    $clonedChildComponent = clone $childComponent;
                    $clonedChildComponent->setDataValue('_dataSource', $repeatDataItem);
                    $clonedChildComponent->setMetaValue('isRepeated', true);
                    $order = [
                        'index' => $index,
                        'total' => $total,
                        'first' => 0 === $index,
                        'last'  => $total - 1 === $index,
                    ];
                    $clonedChildComponent->setMetaValue('order', $order);
                    if ($collectionId) {
                        $clonedInherited->setRepeatItem($collectionId, $itemId);
                    }
                    $children[] = $this->builder->build($clonedChildComponent, $clonedInherited);
                    $index++;
                }
            }
            $this->addChildren($children);
        } else {
            $children        = [];
            $index           = 0;
            $childComponents = $component->getChildren();
            $total           = count($childComponents);

            foreach ($childComponents as $index => $childComponent) {
                $order = $component->getMetaValue('order');
                if ('richtext' === $component->getType()) {
                    $order = [
                        'index' => $index,
                        'total' => $total,
                        'first' => 0 === $index,
                        'last'  => $total - 1 === $index,
                    ];
                }
                if ($order) {
                    $childComponent->setMetaValue('order', $order);
                }
                $children[]     = $this->builder->build($childComponent, clone $inherited);
            }

            $this->addChildren($children);
        }
    }

    public function normalize(array $data): array
    {
        $data = $this->normalizeAction($data);
        $data = $this->normalizeHover($data);

        if (isset($data['_repeat'])) {
            $repeat = $data['_repeat'];
            unset($data['_repeat']);
            if (is_string($repeat)) {
                $repeatCollectionId = $repeat;
                $collection         = $this->getCollection($repeat, true);
                if ($collection) {
                    $repeat = $collection->getItemsArray(true, $this->environment, $this->siteData, $this->path);
                } else {
                    $repeat = [];
                }
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
        if (isset($data['_params']) && isset($data['_target'])) {
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

    protected function normalizeRepeat(string|int|bool|array $data, array $rawRepeatData): array
    {
        $repeatOptions = new RepeatOptions(
            intval($data['_options']['offset'] ?? 0),
            intval($data['_options']['length'] ?? 999999),
            !!($data['_options']['shuffle'] ?? false),
            intval($data['_options']['duplicate'] ?? 0),
            $data['_options']['sortBy'] ?? null,
            $data['_options']['sort'] ?? null,
            $data['_options']['filter'] ?? null,
            $data['_options']['filterField'] ?? null,
            $data['_options']['filterType'] ?? null,
            $data['_options']['filterPattern'] ?? null
        );
        $repeat = new Repeat($rawRepeatData, $repeatOptions);

        $components = array_filter($data, function ($key): bool {
            return !str_starts_with($key, '_');
        }, ARRAY_FILTER_USE_KEY);
        foreach (array_keys($components) as $key) {
            unset($data[$key]);
        }
        $data['_repeatData'] = $repeat->getItems();

        if ($repeat->isEmpty()) {
            unset($data['_repeatData']);
            $data['_isEmpty'] = true;
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

class RepeatOptions
{
    public function __construct(
        public int $offset = 0,
        public int $length = 999999,
        public bool $shuffle = false,
        public int $duplicate = 0,
        public ?string $sortBy = null,
        public ?string $sort = null,
        public ?string $filter = null,
        public ?string $filterField = null,
        public ?string $filterType = 'or',
        public ?string $filterPattern = null,
    ) {
        if (!$this->length) {
            $this->length = 999999;
        }
    }
}

class Repeat
{
    private array $items = [];

    public function __construct(private array $rawItems, private RepeatOptions $options)
    {
        $this->rawItems = $rawItems;
    }

    public function getItems() : array
    {
        $items = $this->rawItems;
        // 1️⃣ Apply shuffle
        if ($this->options->shuffle) {
            // TODO add seed support
            shuffle($items);
        }

        // 2️⃣ Apply filter
        if (isset($this->options->filterField) && (isset($this->options->filter) || isset($this->options->filterType) || isset($this->options->filterPattern))) {
            $filter = new Filter($this->options->filterType, $this->options->filter ?? null, $this->options->filterPattern ?? null);
            $items  = $filter->filterList($items, $this->options->filterField);
        }

        // 3️⃣ Apply offset & length
        $offset = $this->options->offset;
        $length = $this->options->length;
        $items  = array_splice($items, $offset, $length);

        // 4️⃣ Apply sort
        if (isset($this->options->sortBy)) {
            $sortField = $this->options->sortBy;
            uasort($items, function ($a, $b) use ($sortField) {
                if (isset($a[$sortField],$b[$sortField])) {
                    return $a[$sortField] <=> $b[$sortField];
                }
                return 0;
            });
        }
        if (isset($this->options->sort) && 'desc' === $this->options->sort) {
            $items = array_reverse($items);
        }

        // 5️⃣ Apply duplicate
        if ($this->options->duplicate) {
            $count      = is_int($this->options->duplicate) ? $this->options->duplicate : 1;
            $duplicated = [];
            for ($i = 0; $i < $count; $i++) {
                $duplicated = array_merge($duplicated, $items);
            }
            $items = $duplicated;
        }

        // 6️⃣ Add index
        $index0 = 0;
        foreach ($items as &$item) {
            $item['index'] = ++$index0;
        }

        return $items;
    }

    public function isEmpty(): bool
    {
        return count($this->getItems()) === 0;
    }
}
