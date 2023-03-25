<?php

declare(strict_types=1);

namespace Flipsite\Assets\Context;

use Flipsite\Assets\AssetFile;
use Flipsite\Assets\Options\UnsplashOptions;

final class UnsplashContext extends AbstractImageContext
{
    private array $query = [
        'ixid' => '',
        'fm' => 'webp',
        'q' => 90
    ];
    private int $realWidth;
    private int $realHeight;
    private float $scale = 1.0;
    public function __construct(string $image, array $options)
    {
        $tmp = explode('?', $image);
        $query = explode('&', $tmp[1]);
        foreach ($query as $q) {
            $tmp2 = explode('=',$q);
            if ($tmp2[0] === 'w') {
                $this->realWidth = intval($tmp2[1]);
            } elseif ($tmp2[0] === 'h') {
                $this->realHeight = intval($tmp2[1]);
            } else {
                $this->query[$tmp2[0]] = $tmp2[1];
            }
        }
        
        $this->image  = $tmp[0];
        $this->srcset = $options['srcset'] ?? null;
        $size         = $this->getSize($options);
        $this->width  = intval($size['width']);
        $this->height = intval($size['height']);
    }

    public function getSrc(): string
    {
        return $this->buildSrc();
    }

    public function getSrcset(?string $type = null): ?string
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
                $this->scale = floatval(trim($variant, 'x'));
            } else {
                $width = floatval(trim($variant, 'w'));
                $scale = $width / floatval($this->options->getValue('width'));
                $this->scale = floatval(trim($variant, 'x'));
            }
            $srcset[] = new ImageSrcset($this->buildSrc(), $variant, $type);
        }
        return implode(', ', $srcset);
    }

    private function buildSrc(): string
    {
        $this->query['w'] = round($this->width * $this->scale, 0);
        $this->query['h'] = round($this->height * $this->scale, 0);
        return $this->image.'?'.http_build_query($this->query);
    }

    private function getSize(array $options): array
    {
        $width  = $options['width'] ?? null;
        $height = $options['height'] ?? null;
        // If no width, use smallest from srcset
        if (!$width && isset($options['srcset'][0]) && strpos($options['srcset'][0], 'w')) {
            $width = intval($options['srcset'][0]);
        }
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
            $realWidth  = $this->realWidth;
            $realHeight = $this->realHeight;
            if (null === $width && null === $height) {
                $width  = $realWidth;
                $height = $realHeight;
            } elseif (null === $height) {
                $height = intval(round($width / $realWidth * $realHeight));
            } elseif (null === $width) {
                $width = intval(round($height / $realHeight * $realWidth));
            }
        }

        return ['width' => $width, 'height' => $height];
    }
}
