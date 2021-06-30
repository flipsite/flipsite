<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Element extends AbstractElement
{
    public function __construct(string $tag, bool $oneline = false, bool $empty = false)
    {
        $this->tag     = $tag;
        $this->oneline = $oneline;
        $this->empty   = $empty;
    }
}
