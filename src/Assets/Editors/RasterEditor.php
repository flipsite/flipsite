<?php

declare(strict_types=1);

namespace Flipsite\Assets\Editors;

use Flipsite\Assets\Options\RasterOptions;
use Intervention\Image\ImageManager;
use Intervention\Image\Image;

final class RasterEditor extends AbstractImageEditor
{
    public function create(): void
    {
        $manager      = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($this->file->getFilename());
        $filePathinfo = pathinfo($this->path);
        $options      = new RasterOptions($this->path);
        $image = $this->applyOptions($image, $options, $filePathinfo['extension']);
        $quality = $options->getValue('quality') ?? 90;
        switch ($filePathinfo['extension']) {
            case 'webp':
                $encoded = $image->toWebp($quality);
                break;
            case 'png':
                $encoded = $image->toPng();
                break;
            case 'jpg':
                $encoded = $image->toJpeg($quality);
                break;
            case 'gif':
                $encoded = $image->toGif($quality);
                break;
            default:
                throw new \Exception('no format defined');
        };
        $cachedFilename = $this->getCachedFilename();
        $cachedPathinfo = pathinfo($cachedFilename);
        $this->fileSystem->mkdir($cachedPathinfo['dirname'], 0777);
        $encoded->save($cachedFilename);
    }

    private function applyOptions(Image $image, RasterOptions $options, string $outputFormat): Image
    {
        $width  = $options->getValue('width');
        $height = $options->getValue('height');
        if ($width && $height) {
            $position = $options->getValue('position') ?? 'center';
            // change e.g. left-top to top-left (because different order in tailwind and intervention)
            $tmp = explode('-', $position);
            $tmp = array_reverse($tmp);
            $position = implode('-', $tmp);
            if ('gif' === $outputFormat) {
                $image->resize($width, $height);
            } else {
                $image->fit($width, $height, $position);
            }
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
