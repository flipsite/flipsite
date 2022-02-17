<?php

declare(strict_types=1);
namespace Flipsite\Assets\Context;

use Flipsite\Assets\AssetFile;
use Flipsite\Assets\Options\SvgOptions;
use Flipsite\Utils\SvgData;

final class SvgContext extends AbstractImageContext
{
    private string $hash;

    public function __construct(string $image, string $imgBasePath, AssetFile $file, SvgOptions $options)
    {
        $this->image       = $image;
        $this->imgBasePath = $imgBasePath;
        $this->hash        = $file->getHash();
        $this->options     = $options;
        $svgData           = new SvgData($file->getFilename());
        $this->width       = $svgData->getWidth();
        $this->height      = $svgData->getHeight();
    }

    public function getSrc() : string
    {
        $replace = $this->options.'.'.$this->hash.'.svg';
        return $this->imgBasePath.'/'.str_replace('.svg', $replace, $this->image);
    }

    public function getSources() : ?array
    {
        return null;
    }
}
