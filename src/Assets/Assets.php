<?php

declare(strict_types=1);

namespace Flipsite\Assets;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Attributes\ImageAttributesInterface;
use Flipsite\Assets\Attributes\UnsplashImageAttributes;
use Flipsite\Assets\Attributes\ExternalImageAttributes;
use Flipsite\Assets\Attributes\InternalImageAttributes;

class Assets {
    public function __construct(protected AssetSourcesInterface $assetSources) {
    }
    
    public function getSvg(string $filename): ?SvgInterface
    {
        return null;
    }

    public function getImageAttributes(string $image, array $options): ?ImageAttributesInterface
    {
        if (0 === mb_strpos($image, 'http')) {
            if (str_starts_with($image, 'https://images.unsplash.com')) {
                return new UnsplashImageAttributes($image, $options);
            }
            return new ExternalImageAttributes($image);
        }
        $imageInfo = $this->assetSources->getImageInfo($image);
        if ($imageInfo) {
            return new InternalImageAttributes($image, $options, $imageInfo, $this->assetSources);
        }
        return null;
    }
}


