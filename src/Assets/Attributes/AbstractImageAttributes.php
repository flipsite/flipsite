<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

use Flipsite\Assets\Sources\ImageInfoInterface;

abstract class AbstractImageAttributes implements ImageAttributesInterface
{
    protected string $src;
    protected ?array $srcset = null;
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

    protected function setSize(array $options, ImageInfoInterface $imageInfo)
    {
        $width  = $options['width'] ?? null;
        $height = $options['height'] ?? null;

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
                $width  = $imageInfo->getWidth();
                $height = $imageInfo->getHeight();
            } elseif (null === $height) {
                $height = intval(round($width / $imageInfo->getWidth() * $imageInfo->getHeight()));
            } elseif (null === $width) {
                $width = intval(round($height / $imageInfo->getHeight() * $imageInfo->getWidth()));
            }
        }

        $this->width  = $width;
        $this->height = $height;
    }
}
