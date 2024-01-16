<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

class SourceAttributes
{
    public function __construct(private string $src, private ?string $type)
    {
    }

    public function getSrc() : string
    {
        return $this->src;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function getMedia() : ?string
    {
        return null;
    }
}
