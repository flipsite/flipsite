<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class HoverType extends AbstractType
{
    protected int $order      = 200;
    protected string $prefix  = 'hover';
    protected ?string $pseudo = ':hover';
}
