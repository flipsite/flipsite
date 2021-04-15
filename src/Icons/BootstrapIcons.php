<?php

declare(strict_types=1);

namespace Flipsite\Icons;

use Flipsite\Assets\Sources\AbstractAssetSource;

final class BootstrapIcons extends AbstractAssetSource
{
    public function resolve(string $src) : string
    {
        $src = str_replace('.svg', '', $src);
        return $this->packageDir.'/resources/svg/'.$src.'.svg';
    }

    protected function package() : string
    {
        return 'davidhsianturi/blade-bootstrap-icons';
    }
}
