<?php

declare(strict_types=1);

namespace Flipsite\Assets\Context;

use Flipsite\Assets\Options\AbstractImageOptions;

abstract class AbstractImageContext
{
    protected string $src;
    protected ?int $width                    = null;
    protected ?int $height                   = null;
    protected ?array $sources                = null;
    protected ?AbstractImageOptions $options = null;

    public function __construct(string $src)
    {
        $this->src = $src;
    }

    abstract public function getSrc() : string;

    public function getWidth() : ?int
    {
        return $this->width;
    }

    public function getHeight() : ?int
    {
        return $this->height;
    }

    abstract public function getSources() : ?array;
}
