<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Sources\AbstractAssetInfo;

class VideoAttributes implements VideoAttributesInterface
{
    public function __construct(private AbstractAssetInfo $assetInfo, private AssetSourcesInterface $assetSources)
    {
    }

    public function getSources() : array
    {
        $sources   = [];

        $src       = $this->assetInfo->getFilename(false).'.'.$this->assetInfo->getHash().'.'.$this->assetInfo->getExtension();
        $src       = $this->assetSources->addBasePath($this->assetInfo->getType(), $src);
        $sources[] = new SourceAttributes($src, $this->assetInfo->getMimetype());

        return $sources;
    }
}
