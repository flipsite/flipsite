<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class LastType extends AbstractType
{
    protected int $order      = 250;
    protected string $prefix  = 'last';
    protected ?string $pseudo = ':last-child';
}
