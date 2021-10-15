<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class ScrollType extends AbstractType
{
    protected string $prefix = 'scroll';
    protected int $order = 300;
    protected string $parent = 'scroll';
}
