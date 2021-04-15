<?php

declare(strict_types=1);

namespace Flipsite\Assets\Context;

use Flipsite\Assets\ImageFile;
use Flipsite\Assets\Options\SvgOptions;
use Flipsite\Utils\SvgData;

final class SvgContext extends AbstractImageContext
{
    private string $hash;

    public function __construct(string $src, ImageFile $file, SvgOptions $options)
    {
        $this->src     = $src;
        $this->hash    = $file->getHash();
        $this->options = $options;
        $svgData       = new SvgData($file->getFilename());
        $this->width   = $svgData->getWidth();
        $this->height  = $svgData->getHeight();
    }

    public function getSrc() : string
    {
        $replace = $this->options.'.'.$this->hash.'.svg';
        return str_replace('.svg', $replace, $this->src);
    }

    public function getSources() : ?array
    {
        return null;
    }
}
