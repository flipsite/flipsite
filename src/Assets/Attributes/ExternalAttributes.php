<?php

declare(strict_types=1);

namespace Flipsite\Assets\Attributes;

class ExternalAttributes implements ImageAttributesInterface
{
    public function __construct(private string $src)
    {
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function getSrcset(?string $type = null): ?string
    {
        return null;
    }

    public function getWidth(): ?int
    {
        return null;
    }

    public function getHeight(): ?int
    {
        return null;
    }
}
