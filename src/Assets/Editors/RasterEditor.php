<?php

declare(strict_types=1);

namespace Flipsite\Assets\Editors;

use Intervention\Image\ImageManager;
use Intervention\Image\Image;
use Flipsite\Assets\Options\RasterOptions;
use Intervention\Image\EncodedImage;
use Flipsite\Assets\Sources\AbstractAssetInfo;

final class RasterEditor
{
    public function __construct(private RasterOptions $options, private AbstractAssetInfo $assetInfo, private string $outputFormat) {

    }
    public function getImage(): EncodedImage
    {
        $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($this->assetInfo->getContents());
        $image = $this->applyOptions($image);

        $quality = $this->options->getValue('quality') ?? 90;
        switch ($this->outputFormat) {
            case 'webp':
                return $image->toWebp($quality);
            case 'png':
                return $image->toPng();
            case 'jpg':
                return $image->toJpeg($quality);
            case 'gif':
                return $image->toGif($quality);        
        };
        throw new \Exception('no output format defined');
    }

    private function applyOptions(Image $image): Image
    {
        $width  = $this->options->getValue('width');
        $height = $this->options->getValue('height');

        if ($width && $height) {
            // $position = $this->options->getValue('position') ?? 'center';
            // // change e.g. left-top to top-left (because different order in tailwind and intervention)
            // $tmp = explode('-', $position);
            // $tmp = array_reverse($tmp);
            // $position = implode('-', $tmp);
            $image->fit($width, $height);
        } elseif ($width) {
            $image->scale(width: $width);
        } elseif ($height) {
            $image->scale(height: $height);
        }
        $blur = $this->options->getValue('blur');
        if ($blur) {
            $image->blur($blur);
        }
        $opacity = $this->options->getValue('opacity');
        if ($opacity) {
            $image->opacity($opacity);
        }
        if ($this->options->getValue('blackWhite')) {
            $image->greyscale();
        }
        $pixelate = $this->options->getValue('pixelate');
        if ($this->options->getValue('pixelate')) {
            $image->pixelate($pixelate);
        }
        if ($this->options->getValue('invert')) {
            $image->invert();
        }


        return $image;
    }
}
