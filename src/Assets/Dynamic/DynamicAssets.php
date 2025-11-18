<?php

declare(strict_types=1);
namespace Flipsite\Assets\Dynamic;

use Flipsite\Data\SiteDataInterface;
use Flipsite\Assets\Sources\AssetSourcesInterface;

class DynamicAssets implements DynamicAssetsInterface
{
    public function __construct(private SiteDataInterface $siteData, private AssetSourcesInterface $assetSources)
    {
    }

    public function isAsset(string $asset): bool
    {
        $dynamicAssets = $this->getInterface($asset);
        if (!$dynamicAssets) {
            return false;
        }
        return $dynamicAssets->isAsset($asset);
    }

    public function getContents(string $asset): string
    {
        $dynamicAssets = $this->getInterface($asset);
        if (!$dynamicAssets) {
            return '';
        }
        return $dynamicAssets->getContents($asset);
    }

    public function getMimetype(string $asset): string
    {
        $dynamicAssets = $this->getInterface($asset);
        if (!$dynamicAssets) {
            return '';
        }
        return $dynamicAssets->getMimetype($asset);
    }

    private function getInterface(string $asset): ?DynamicAssetsInterface
    {
        if (str_ends_with($asset, 'vcf')) {
            return new DynamicVcf($this->siteData, $this->assetSources);
        }
        if (str_ends_with($asset, 'ics')) {
            return new DynamicIcs($this->siteData, $this->assetSources);
        }
        return null;
    }
}
