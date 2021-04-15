<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class ResponsiveType extends AbstractType
{
    protected int $order = 300;

    public function __construct(string $prefix, int $screenSize)
    {
        $this->prefix     = $prefix;
        $this->mediaQuery = '(min-width:'.$screenSize.'px)';
        $this->order += intval($screenSize / 100);
    }
}
