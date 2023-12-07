<?php

declare(strict_types=1);

namespace Flipsite\Assets;

interface AssetsInterface
{
    public function getSvg(string $filename) : ?SvgInterface;
    public function getImageAttributes(string $filename, array $options) : ?ImageAttributesInterface;
}
