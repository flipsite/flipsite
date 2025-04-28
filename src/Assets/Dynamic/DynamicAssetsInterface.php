<?php

declare(strict_types=1);

namespace Flipsite\Assets\Dynamic;

interface DynamicAssetsInterface
{
    public function isAsset(string $asset): bool;
    public function getContents(string $asset): string;
    public function getMimetype(string $asset): string;
}
