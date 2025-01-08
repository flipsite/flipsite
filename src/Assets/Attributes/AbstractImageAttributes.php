<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

abstract class AbstractImageAttributes implements ImageAttributesInterface
{
    protected string $src;
    protected ?array $srcset  = null;
    protected ?int $width     = null;
    protected ?int $height    = null;

    public function getSrc(): string
    {
        return $this->src;
    }

    public function getSrcset(?string $type = null): ?string
    {
        return $this->srcset;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    protected function setSize(array $options, int $realWidth, int $realHeight)
    {
        $width   = isset($options['width']) ? intval($options['width']) : null;
        $height  = isset($options['height']) ? intval($options['height']) : null;

        if (isset($options['aspectRatio'])) {
            $tmp = explode('by', str_replace('/', 'by', $options['aspectRatio']));
            if (2 === count($tmp)) {
                $factor = floatval($tmp[0] / $tmp[1]);
                if ($width) {
                    $height = intval($width / $factor);
                } elseif ($height) {
                    $width = intval($height / $factor);
                }
            }
        }

        if (null === $width || null === $height) {
            if (null === $width && null === $height) {
                $max = 1024;
                if ($realWidth <= $max || $realHeight <= $max) {
                    $width  = $realWidth;
                    $height = $realHeight;
                } elseif ($realWidth > $realHeight) {
                    $width  = $max;
                    $height = intval(round($max / $realWidth * $realHeight));
                } else {
                    $height = $max;
                    $width  = intval(round($max / $realHeight * $realWidth));
                }
            } elseif (null === $height) {
                $height = intval(round($width / $realWidth * $realHeight));
            } elseif (null === $width) {
                $width = intval(round($height / $realHeight * $realWidth));
            }
        }

        $this->width  = $width;
        $this->height = $height;
    }
}
