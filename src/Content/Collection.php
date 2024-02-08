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
        $this->name = $rawSchema['_name'] ?? $id;
        $this->icon = $rawSchema['_icon'] ?? null;
        unset($rawSchema['_name'], $rawSchema['_icon']);

        $this->schema = new Schema($rawSchema);
        foreach ($this->rawItems as $index => $rawItem) {
            $rawItem['id'] ??= $index+1;
            $this->addItem($rawItem);
        }
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getItems(bool $onlyPublished = false) : array
    {
        if ($onlyPublished && $this->schema->hasPublishedField()) {
            return array_filter($this->items, function ($item) {
                return $item->isPublished();
            });
        }
        return $this->items;
    }

    public function getItemsArray(bool $onlyPublished = false) : array
    {
        $items = $this->getItems($onlyPublished);
        return json_decode(json_encode($items), true);
    }

    public function getItem(?int $itemId = null) : ?Item
    {
        return $this->items[$itemId ?? -1] ?? null;
    }

    public function getSlugField() : ?string {
        foreach ($this->schema as $field => $val) {
            if (is_array($val) && 'slug' === ($val['type'] ?? '')) {
                return $field;
            }
        }
        return null;
    }

    public function addItem(array $rawItem) : Item
    {
        if (!isset($rawItem['id'])) {
            $nextId = 1;
            foreach ($this->items as $item) {
                $nextId = max($nextId, $item->getId());
            }
            $nextId++;
            $rawItem['id'] = $nextId;
        }
        $item = new Item($this->schema, $rawItem);
        $this->items[$item->getId()] = $item;
        return $item;
    }
    public function deleteItem(int $itemId) : void
    {
        foreach ($this->items as $index => $item) {
            if ($item->getId() === $itemId) {
                unset($this->items[$index]);
                break;
            }
        }
    }
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'schema' => $this->schema,
            'items' => array_values($this->items)
        ];
    }
}
