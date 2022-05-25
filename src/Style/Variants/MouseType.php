<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class MouseType extends AbstractType
{
    protected int $order          = 400;
    protected string $prefix      = 'mouse';
    protected ?string $mediaQuery = '(pointer: fine)';
}
