<?php

declare(strict_types=1);
namespace Flipsite\Assets\Context;

use Flipsite\Assets\Options\AbstractImageOptions;

abstract class AbstractImageContext
{
    protected string $image;
    protected string $imgBasePath;
    protected ?int $width                    = null;
    protected ?int $height                   = null;
    protected ?AbstractImageOptions $options = null;

    abstract public function getSrc() : string;

    public function getWidth() : ?int
    {
        return $this->width;
    }

    public function getHeight() : ?int
    {
        return $this->height;
    }
}
