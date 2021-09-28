<?php

declare(strict_types=1);
namespace Flipsite\Assets\Context;

final class ImageSrcset
{
    public string $src;
    public ?string $variant;

    public function __construct(string $src, ?string $variant = null)
    {
        $this->src     = $src;
        $this->variant = $variant;
    }

    public function __toString() : string
    {
        if (isset($this->variant)) {
            return $this->src.' '.$this->variant;
        }
        return $this->src;
    }
}
