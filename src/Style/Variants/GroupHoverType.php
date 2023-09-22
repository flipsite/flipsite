<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class GroupHoverType extends AbstractType
{
    protected int $order     = 300;
    protected string $prefix = 'group-hover';
    protected string $parent = 'group:hover';

    public function __construct(string $prefix, ?string $name = null)
    {
        if ($name) {
            $this->prefix = 'group-hover\/'.$name;
            $this->parent = 'group\/'.$name.':hover';
        }
    }
}
