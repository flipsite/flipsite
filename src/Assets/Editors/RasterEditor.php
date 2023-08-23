<?php

declare(strict_types=1);

namespace Flipsite\Assets\Editors;

use Flipsite\Assets\Options\RasterOptions;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

final class RasterEditor extends AbstractImageEditor
{
    public function create(): void
    {
        $manager      = new ImageManager();
        $image        = $manager->make($this->file->getFilename());
        $filePathinfo = pathinfo($this->path);
        $options      = new RasterOptions($this->path);
        $image        = $this->applyOptions($image, $options);
        // Requested file has different format than actual asset
        if ($this->file->getExtension() !== $filePathinfo['extension']) {
            $quality = $options->getValue('quality') ?? 90;
            $image->encode($this->file->getExtension(), $quality);
        }
        $cachedFilename = $this->getCachedFilename();
        $cachedPathinfo = pathinfo($cachedFilename);
        $this->fileSystem->mkdir($cachedPathinfo['dirname'], 0777);
        $image->save($cachedFilename);
    }

    private function applyOptions(Image $image, RasterOptions $options): Image
    {
        $width  = $options->getValue('width');
        $height = $options->getValue('height');
        if ($width && $height) {
            $position = $options->getValue('position') ?? 'center';
            // change e.g. left-top to top-left (because different order in tailwind and intervention)
            $tmp = explode('-', $position);
            $tmp = array_reverse($tmp);
            $position = implode('-', $tmp);
            $image->fit($width, $height, null, $position);
        } elseif ($width) {
            $image->resize($width, null, static function ($constraint): void {
                $constraint->aspectRatio();
            });
        } elseif ($height) {
            $image->resize(null, $height, static function ($constraint): void {
                $constraint->aspectRatio();
            });
        }
        $blur = $options->getValue('blur');
        if ($blur) {
            $image->blur($blur);
        }
        $opacity = $options->getValue('opacity');
        if ($opacity) {
            $image->opacity($opacity);
        }
        if ($options->getValue('blackWhite')) {
            $image->greyscale();
        }

        return $image;
    }
}
