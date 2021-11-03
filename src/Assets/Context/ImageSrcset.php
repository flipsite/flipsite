<?php

declare(strict_types=1);
namespace Flipsite\Assets\Context;

final class ImageSrcset
{
    public function __construct(public string $src, public ?string $variant = null, public ?string $type = null)
    {
    }

    public function __toString() : string
    {
        if ('url' === $this->type) {
            if (isset($this->variant)) {
                return 'url('.$this->src.') '.$this->variant;
            }
            return 'url('.$this->src.')';
        }
        if (isset($this->variant)) {
            return $this->src.' '.$this->variant;
        }
        return $this->src;
    }
}
