<?php

declare(strict_types=1);

namespace Flipsite\Assets\Dynamic;

use Flipsite\Data\SiteDataInterface;

class DynamicVcf implements DynamicAssetsInterface
{
    public function __construct(array $data, private SiteDataInterface $siteData)
    {
        unset($data['_type']);
        echo $data['_collectionId'];
    }
    public function isAsset(string $asset): bool
    {
        echo $asset;
        return false;
    }
    public function getContents(string $asset): string
    {
        return '';
    }
    public function getMimetype(string $asset): string
    {
        return 'text/vcard';
    }
}
