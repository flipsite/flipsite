<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class GroupHoverType extends AbstractType
{
    protected int $order     = 300;
    protected string $prefix = 'group-hover';
    protected string $parent = 'group:hover';
}
