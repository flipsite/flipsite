<?php

declare(strict_types=1);
namespace Flipsite\Content;

class Collection implements \JsonSerializable
{
    public function __construct(private string $id, private array $schema, private array $items)
    {
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getContent(bool $onlyPublished = false) : array
    {
        $items = $this->items;
        if ($onlyPublished && isset($this->schema['published'])) {
            $items = array_filter($items, function ($item) {
                return $item['published'] ?? false;
            });
        }
        return $items;
    }

    public function getItem(?int $itemId = null) : ?Item
    {
        if (null === $itemId) {
            return new Item(0, $this->items[0]);    
        }
        return new Item($itemId, $this->items[$itemId]);
    }

    public function jsonSerialize(): mixed
    {
        return $this->schema;
    }
}
