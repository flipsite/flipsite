<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class PrintType extends AbstractType
{
    protected int $order          = 1000;
    protected string $prefix      = 'print';
    protected ?string $mediaQuery = ' print';
}
