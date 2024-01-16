<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

use Flipsite\Assets\Sources\ImageInfoInterface;

interface VideoAttributesInterface
{
    public function getSources() : array;
}
