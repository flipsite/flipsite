<?php

declare(strict_types=1);
namespace Flipsite\Content;

class Collection implements \JsonSerializable
{
    private string $name;
    private ?string $icon;
    private Schema $schema;
    private array $items = [];

    public function __construct(private string $id, private array $rawSchema, private ?array $rawItems)
    {
        $this->name     = $rawSchema['_name'] ?? $id;
        $this->icon     = $rawSchema['_icon'] ?? null;
        if ($this->icon) {
            $this->icon = lcfirst(strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->icon)));
            $this->icon = str_replace('icon-', '', $this->icon);
        }

        unset($rawSchema['_name'], $rawSchema['_icon'], $rawSchema['_internal']);

        $this->schema = new Schema($rawSchema);
        if ($this->rawItems) {
            foreach ($this->rawItems as $index => $rawItem) {
                $rawItem['_id'] ??= $index + 1;
                $this->addItem($rawItem);
            }
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getItems(bool $onlyPublished = false): array
    {
        $publishedFieldId = $this->schema->getFieldsOfType('published')[0] ?? null;
        if ($onlyPublished && $publishedFieldId) {
            return array_filter($this->items, function ($item) use ($publishedFieldId) {
                return !!$item->get($publishedFieldId);
            });
        }
        return $this->items;
    }

    public function getItemsArray(bool $onlyPublished = false): array
    {
        $items = $this->getItems($onlyPublished);
        return json_decode(json_encode($items), true);
    }

    public function getItem(?int $itemId = null): ?Item
    {
        $item = $this->items[$itemId ?? -1] ?? null;
        return $item;
    }

    public function getSlugField(): ?string
    {
        return $this->schema->getFieldsOfType('slug')[0] ?? null;
    }

    public function addField(array $rawField): bool
    {
        if (!$rawField['name'] || $this->schema->hasField($rawField['name'])) {
            return false;
        }
        $this->schema->addField($rawField);
        foreach ($this->items as &$item) {
            $item->setSchema($this->schema);
            $item->applyDelta([], true);
        }
        return true;
    }

    public function editField(string $fieldId, array $delta): bool
    {
        if (isset($delta['name']) && $this->schema->hasField($delta['name'])) {
            return false;
        }
        $newFieldName = $this->schema->editField($fieldId, $delta);
        foreach ($this->items as &$item) {
            if ($newFieldName) {
                $item->renameField($fieldId, $newFieldName);
            }
            $item->setSchema($this->schema);
            $item->applyDelta([], true);
        }
        return true;
    }

    public function addItem(array $rawItem, ?int $index = null): Item
    {
        if (!isset($rawItem['_id'])) {
            $nextId = 0;
            foreach ($this->items as $item) {
                $nextId = max($nextId, $item->getId());
            }
            $nextId++;
            $rawItem['_id'] = $nextId;
        }
        $rawItem['_collectionId']    = $this->id;
        $item                        = new Item($this->schema, $rawItem, $this->id);
        $this->items[$item->getId()] = $item;

        if ($index !== null) {
            $items    = $this->items;
            $lastItem = array_pop($items);
            array_splice($items, $index, 0, [$lastItem]);
            $this->items = [];
            foreach ($items as $itm) {
                $this->items[$itm->getId()] = $itm;
            }
        }

        return $item;
    }

    public function sortItemsByField(string $field, string $direction)
    {
        uasort($this->items, function ($a, $b) use ($field, $direction) {
            $a = $a->get($field);
            $b = $b->get($field);
            if (is_numeric($a) && is_numeric($b)) {
                $a = (float) $a;
                $b = (float) $b;
                return 'asc' === $direction ? $a - $b : $b - $a;
            }
            return 'asc' === $direction ? $a <=> $b : $b <=> $a;
        });
    }

    public function jsonSerialize(): mixed
    {
        $json = [
            'id'       => $this->id,
            'name'     => $this->name,
            'icon'     => $this->icon,
            'schema'   => $this->schema,
            'items'    => array_values($this->items)
        ];
        if (!$this->icon) {
            unset($json['icon']);
        }
        return $json;
    }
}
