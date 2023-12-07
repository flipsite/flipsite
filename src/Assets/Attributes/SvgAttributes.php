<?php

declare(strict_types=1);

namespace Flipsite\Assets\Attributes;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Sources\ImageInfoInterface;

class SvgAttributes implements ImageAttributesInterface
{
    public function __construct(private ImageInfoInterface $imageInfo, private AssetSourcesInterface $assetSources)
    {
    }

    public function getSrc(): string
    {
        $src = $this->imageInfo->getFilename();
        $src = str_replace('.svg', '.'.$this->imageInfo->getHash().'.svg',$src);
        return $this->assetSources->addImageBasePath($src);
    }

    public function getSrcset(?string $type = null): ?string
    {
        return null;
    }

    public function getWidth(): ?int
    {
        return null;
    }

    public function getHeight(): ?int
    {
        return null;
    }
}
