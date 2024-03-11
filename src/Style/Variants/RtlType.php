<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class RtlType extends AbstractType
{
    protected int $order      = 250;
    protected string $prefix  = 'rtl';
    protected ?string $pseudo = ':where([dir="rtl"], [dir="rtl"] *)';
}
