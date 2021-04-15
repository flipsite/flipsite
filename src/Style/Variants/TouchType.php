<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class TouchType extends AbstractType
{
    protected int $order          = 400;
    protected string $prefix      = 'touch';
    protected ?string $mediaQuery = '(hover: none) and (pointer: coarse)';
}
