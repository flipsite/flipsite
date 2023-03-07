<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class EvenType extends AbstractType
{
    protected int $order      = 250;
    protected string $prefix  = 'even';
    protected ?string $pseudo = ':nth-child(even)';
}
