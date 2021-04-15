<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Element extends AbstractElement
{
    public function __construct(string $type, bool $oneline = false, bool $empty = false)
    {
        $this->type    = $type;
        $this->oneline = $oneline;
        $this->empty   = $empty;
    }
}
