<?php

declare(strict_types=1);

namespace Flipsite\Assets\Attributes;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Sources\AbstractAssetInfo;

class SvgAttributes implements ImageAttributesInterface
{
    public function __construct(private AbstractAssetInfo $assetInfo, private AssetSourcesInterface $assetSources)
    {
    }

    public function getSrc(): string
    {
        $src = $this->assetInfo->getFilename();
        $src = str_replace('.svg', '.'.$this->assetInfo->getHash().'.svg',$src);
        return $this->assetSources->addBasePath($this->assetInfo->getType(), $src);
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
