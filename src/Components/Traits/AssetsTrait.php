<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Assets\Assets;

trait AssetsTrait
{
    protected Assets $assets;

    public function addAssets(Assets $assets) : void
    {
        $this->assets = $assets;
    }
}
