<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;

interface AssetSourcesInterface
{
    public function getImageInfo(string $image) : ?ImageInfoInterface;
    public function addImageBasePath(string $image) : string;
}
