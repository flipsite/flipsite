<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class OddType extends AbstractType
{
    protected int $order      = 250;
    protected string $prefix  = 'odd';
    protected ?string $pseudo = ':nth-child(odd)';
}
