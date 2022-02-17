<?php

declare(strict_types=1);
namespace Flipsite\Assets\Context;

use Flipsite\Assets\AssetFile;
use Flipsite\Assets\Options\RasterOptions;

final class RasterContext extends AbstractImageContext
{
    private string $hash;
    private bool $webp;
    private ?array $srcset;
    private string $extension;

    public function __construct(string $image, string $imgBasePath, AssetFile $file, array $options)
    {
        $this->image                  = $image;
        $pathinfo                     = pathinfo($image);
        $this->extension              = $pathinfo['extension'];
        $this->imgBasePath            = $imgBasePath;
        $this->hash                   = $file->getHash();
        $this->srcset                 = $options['srcset'] ?? null;
        $size                         = $this->getSize($options, $file->getFilename());
        $options['width']             = $size['width'];
        $options['height']            = $size['height'];
        $this->width                  = $options['width'];
        $this->height                 = $options['height'];
        unset($options['aspectRatio'], $options['srcset']);
        $this->options = new RasterOptions($options);
    }

    public function getSrc() : string
    {
        return $this->imgBasePath.'/'.$this->buildSrc();
    }

    public function getSrcset(?string $type = null) : ?string
    {
        if (null === $this->srcset) {
            return null;
        }
        $srcset = [];
        foreach ($this->srcset as $variant) {
            preg_match('/[0-9\.]+[w|x]/', $variant, $matches);
            if (0 === count($matches)) {
                throw new \Exception('Invalid srcset variant (' . $variant . '). Should be multiplier (1x, 1.5x) or width (100w, 300w)');
            }
            if (false !== mb_strpos($variant, 'x')) {
                $this->options->changeScale(floatval(trim($variant, 'x')));
            } else {
                $width = floatval(trim($variant, 'w'));
                $scale = $width / floatval($this->options->getValue('width'));
                $this->options->changeScale($scale);
            }
            $srcset[] = new ImageSrcset($this->imgBasePath.'/'.$this->buildSrc(), $variant, $type);
        }
        $this->options->changeScale();
        return implode(', ', $srcset);
    }

    private function buildSrc() : string
    {
        $replace = $this->options.'.'.$this->hash. '.'.$this->extension;
        return str_replace('.'.$this->extension, $replace, $this->image);
    }

    private function getSize(array $options, string $filename) : array
    {
        $width  = $options['width'] ?? null;
        $height = $options['height'] ?? null;
        // If no width, use smallest from srcset
        if (!$width && isset($options['srcset'][0]) && strpos($options['srcset'][0], 'w')) {
            $width = intval($options['srcset'][0]);
        }
        if (isset($options['aspectRatio'])) {
            $tmp = explode('by', $options['aspectRatio']);
            if (2 === count($tmp)) {
                $factor = floatval($tmp[0] / $tmp[1]);
                if ($width) {
                    $height = intval($width / $factor);
                } elseif ($height) {
                    $width = intval($height / $factor);
                }
            }
        }

        return ['width' => $width, 'height' => $height];
    }
}
