<?php

declare(strict_types=1);

namespace Flipsite\Assets\Context;

use Flipsite\Assets\ImageFile;
use Flipsite\Assets\Options\RasterOptions;

final class RasterContext extends AbstractImageContext
{
    private string $filename;
    private string $hash;
    private string $extension;
    private ?array $srcset;
    private bool $webp;

    public function __construct(string $src, ImageFile $file, RasterOptions $options, ?array $srcset = null, bool $webp = true)
    {
        $this->src       = $src;
        $this->hash      = $file->getHash();
        $this->extension = $file->getExtension();
        $this->filename  = $file->getFilename();
        $this->options   = $options;
        $this->srcset    = $srcset;
        $this->webp      = $webp;

        $this->width  = $options->getValue('width');
        $this->height = $options->getValue('height');

        if (!$this->width && !$this->height) {
            $realSize     = getimagesize($this->filename);
            $this->width  = $realSize[0];
            $this->height = $realSize[1];
        } elseif (!$this->width) {
            $realSize    = getimagesize($this->filename);
            $factor      = floatval($this->height / $realSize[1]);
            $this->width = intval($factor * $realSize[0]);
        } elseif (!$this->height) {
            $realSize     = getimagesize($this->filename);
            $factor       = floatval($this->width / $realSize[0]);
            $this->height = intval($factor * $realSize[1]);
        }
    }

    public function getSrc() : string
    {
        return $this->buildSrc('webp' === $this->extension ? 'png' : $this->extension);
    }

    public function getSources() : ?array
    {
        $sources = [];
        if ($this->webp) {
            $sources[] = new ImageSource('image/webp', $this->getSrcset('webp'));
        }
        $extension = 'webp' === $this->extension ? 'png' : $this->extension;
        $sources[] = new ImageSource('image/'.$extension, $this->getSrcset($extension));
        return $sources;
    }

    public function getSrcset(string $extension) : array
    {
        if (null === $this->srcset) {
            return [new ImageSrcset($this->buildSrc($extension))];
        }
        $srcset = [];
        foreach ($this->srcset as $variant) {
            preg_match('/[0-9\.]+[w|x]/', $variant, $matches);
            if (0 === count($matches)) {
                throw new \Exception('Invalid srcset variant ('.$variant.'). Should be multiplier (1x, 1.5x) or width (100w, 300w)');
            }
            if (false !== mb_strpos($variant, 'x')) {
                $this->options->changeScale(floatval(trim($variant, 'x')));
            } else {
                $width = floatval(trim($variant, 'w'));
                $scale = $width / floatval($this->options->getValue('width'));
                $this->options->changeScale($scale);
            }
            $srcset[] = new ImageSrcset($this->buildSrc($extension), $variant);
        }
        $this->options->changeScale();
        return $srcset;
    }

    private function buildSrc(string $extension) : string
    {
        $replace = $this->options.'.'.$this->hash.'.'.$extension;
        return str_replace('.'.$this->extension, $replace, $this->src);
    }
}
