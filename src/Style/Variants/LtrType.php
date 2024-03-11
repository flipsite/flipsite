<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class LtrType extends AbstractType
{
    protected int $order      = 250;
    protected string $prefix  = 'ltr';
    protected ?string $pseudo = ':where([dir="ltr"], [dir="ltr"] *)';
}
