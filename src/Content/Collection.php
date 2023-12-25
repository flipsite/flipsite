<?php

declare(strict_types=1);
namespace Flipsite\Content;

class Collection implements \JsonSerializable
{
    public function __construct(private string $id, private array $schema, private ?array $items)
    {
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getContent(bool $onlyPublished = false) : array
    {
        $items = $this->items ?? [];
        if ($onlyPublished && isset($this->schema['published'])) {
            $items = array_filter($items, function ($item) {
                return $item['published'] ?? false;
            });
        }
        return $items;
    }
    
    public function getSlugField() : ?string {
        foreach ($this->schema as $field => $val) {
            if (is_array($val) && 'slug' === ($val['format'] ?? '')) {
                return $field;
            }
        }
        return null;
    }

    public function jsonSerialize(): mixed
    {
        return $this->schema;
    }
}
