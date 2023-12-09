<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

class ExternalVideoAttributes extends VideoAttributesInterface
{
    public function __construct(private string $src)
    {
        public function getSources() : array
    }
    
}

