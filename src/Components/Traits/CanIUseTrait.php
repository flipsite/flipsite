<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Utils\CanIUse;

trait CanIUseTrait
{
    protected CanIUse $canIUse;

    public function addCanIUse(CanIUse $canIUse) : void
    {
        $this->canIUse = $canIUse;
    }
}
