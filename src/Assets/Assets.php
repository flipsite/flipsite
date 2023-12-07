<?php

declare(strict_types=1);

namespace Flipsite\Assets;

final class Assets implements AssetsInterface
{
    public function __construct() {}

    public function getSvg(string $filename): ?SvgInterface
    {
        return null;
    }
    public function getImageAttributes(string $image, array $options): ?ImageAttributesInterface
    {
        return $a = new UnsplashImageAttributes($image, $options);

        // echo $filename."\n";
        // print_r($options);
        return null;
    }
}

abstract class RasterImageAttributes implements ImageAttributesInterface
{
    public function getSrc(): string
    {
        return $this->src;
    }
    public function getSrcset(?string $type = null): ?string
    {
        return null;//$this->srcset;
    }
    public function getWidth(): int
    {
        return $this->width;
    }
    public function getHeight(): int
    {
        return $this->height;
    }
}
class UnsplashImageAttributes extends RasterImageAttributes {
    public function __construct(string $url, array $options) {

        $this->src = $url;
        preg_match('/[?&]w=([^&]+)/', $url, $matches_w);
        preg_match('/[?&]h=([^&]+)/', $url, $matches_h);

        $this->width = intval($matches_w[1] ?? null);
        $this->height = intval($matches_h[1] ?? null);

        

        // $tmp = explode('?', $url);
        // $query = explode('&', $tmp[1]);
        // foreach ($query as $q) {
        //     $tmp2 = explode('=', $q);
        //     if ($tmp2[0] === 'w') {
        //         $this->realWidth = intval($tmp2[1]);
        //     } elseif ($tmp2[0] === 'h') {
        //         $this->realHeight = intval($tmp2[1]);
        //     } else {
        //         $this->query[$tmp2[0]] = $tmp2[1];
        //     }
        // }

        // $this->image  = $tmp[0];
        // $this->srcset = $options['srcset'] ?? null;
        // $size         = $this->getSize($options);
        // $this->width  = intval($size['width']);
        // $this->height = intval($size['height']);
    }
}
