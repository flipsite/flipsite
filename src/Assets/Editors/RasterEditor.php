<?php

declare(strict_types=1);
namespace Flipsite\Assets\Editors;

use Flipsite\Assets\Options\RasterOptions;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

final class RasterEditor extends AbstractImageEditor
{
    public function create() : void
    {
        $manager      = new ImageManager();
        $image        = $manager->make($this->file->getFilename());
        $filePathinfo = pathinfo($this->path);
        $image        = $this->applyOptions($image, $filePathinfo['extension']);
        // Requested file has different format than actual asset
        if ($this->file->getExtension() !== $filePathinfo['extension']) {
            $image->encode($this->file->getExtension(), 90); //80 if jpg
        }
        $cachedFilename = $this->getCachedFilename();
        $cachedPathinfo = pathinfo($cachedFilename);
        $this->fileSystem->mkdir($cachedPathinfo['dirname'], 0777);
        $image->save($cachedFilename);
    }

    private function applyOptions(Image $image, string $extension) : Image
    {
        $options = new RasterOptions($this->path);

        $trim  = (bool)$options->getValue('trim') ?? false;
        if ($trim) {
            $image->trim('top-left', null, 10);
            $image->trim('transparent');
        }
        $width   = $options->getValue('width');
        $height  = $options->getValue('height');
        if ($width && $height) {
            $image->fit($width, $height);
        } elseif ($width) {
            $image->resize($width, null, static function ($constraint) : void {
                $constraint->aspectRatio();
            });
        } elseif ($height) {
            $image->resize(null, $height, static function ($constraint) : void {
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
