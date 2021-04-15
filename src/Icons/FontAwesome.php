<?php

declare(strict_types=1);

namespace Flipsite\Icons;

use Flipsite\Assets\Sources\AbstractAssetSource;

final class FontAwesome extends AbstractAssetSource
{
    public function resolve(string $src) : string
    {
        $src = str_replace('.svg', '', $src);
        return $this->packageDir.'/svgs/'.$src.'.svg';
    }

    protected function package() : string
    {
        return 'fortawesome/font-awesome';
    }
}
