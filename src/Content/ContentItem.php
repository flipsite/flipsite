<?php

declare(strict_types=1);
namespace Flipsite\Content;
 
class ContentItem implements \JsonSerializable
{
    public function __construct(ContentSchema $schema, private array $data, ?int $index = null) {
    }
    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

}