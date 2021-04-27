<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class ResponsiveType extends AbstractType
{
    protected int $order = 300;

    public function __construct(string $prefix, int $screenSize)
    {
        if (is_numeric($prefix[0])) {
            $prefix = '\\3'.$prefix;
        }
        $this->prefix     = $prefix;
        $this->mediaQuery = '(min-width:'.$screenSize.'px)';
        $this->order += intval($screenSize / 100);
    }
}
