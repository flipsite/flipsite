<?php

declare(strict_types=1);

namespace Flipsite\Icons;

use Flipsite\Assets\Sources\AbstractAssetSource;

final class SimpleIcons extends AbstractAssetSource
{
    public function resolve(string $src) : string
    {
        $src = str_replace('.svg', '', $src);
        return $this->packageDir.'/icons/'.$src.'.svg';
    }

    protected function package() : string
    {
        return 'simple-icons/simple-icons';
    }
}
