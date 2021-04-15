<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class FocusType extends AbstractType
{
    protected int $order      = 200;
    protected string $prefix  = 'focus';
    protected ?string $pseudo = ':focus';
}
