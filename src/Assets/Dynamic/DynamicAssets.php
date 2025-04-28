<?php

declare(strict_types=1);

namespace Flipsite\Assets\Dynamic;

use Flipsite\Data\SiteDataInterface;

class DynamicAssets implements DynamicAssetsInterface
{
    private array $dynamicAssetTypes;
    public function __construct(private SiteDataInterface $siteData)
    {
        //$this->dynamicAssetTypes = $siteData->getDynamicAssets();
    }
    public function isAsset(string $asset): bool
    {
        $dynamicAssets = $this->getInterface($asset);
        if (!$dynamicAssets) {
            return false;
        }
        $dynamicAssets->isAsset($asset);
    }

    public function getContents(string $asset): string
    {
        return 'BEGIN:VCARD
VERSION:3.0
TITLE:Content Manager
ORG:Example Co.
FN:Jane Smith
N:Smith;Jane;;;
EMAIL;TYPE=work:jane.smith@example.com
TEL;TYPE=cell,voice:(123) 555-7890
ADR;TYPE=work:;;123 Market St;San Francisco;CA;94105;USA
END:VCARD';
    }

    public function getMimetype(string $asset): string
    {
        return 'text/vcard';
    }

    private function getInterface(string $asset): ?DynamicAssetsInterface
    {
        foreach ($this->dynamicAssetTypes as $type) {
            if (str_starts_with($asset, $type)) {
                $class = 'Flipsite\\Assets\\Dynamic\\'.ucfirst($type);
                if (class_exists($class)) {
                    return new $class($this->siteData);
                }
            }
        }
        return null;
    }
}
