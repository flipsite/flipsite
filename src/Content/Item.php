<?php

declare(strict_types=1);
namespace Flipsite\Content;

class Item implements \JsonSerializable
{
    public function __construct(private int $id, private array $data)
    {
    }

    public function get(string $field) : mixed {
        return $this->data[$field] ?? null;
    }
    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
