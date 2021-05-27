<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class FirstType extends AbstractType
{
    protected int $order      = 250;
    protected string $prefix  = 'first';
    protected ?string $pseudo = ':first-child';
}
