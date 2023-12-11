<?php

declare(strict_types=1);
namespace Flipsite\Content;

class ContentSchema implements \JsonSerializable
{
    public function __construct(private string $id, private array $schema)
    {
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function jsonSerialize(): mixed
    {
        return $this->schema;
    }
}
