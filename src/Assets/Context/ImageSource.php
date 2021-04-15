<?php

declare(strict_types=1);

namespace Flipsite\Assets\Context;

final class ImageSource
{
    public string $type;
    public array $srcset;

    public function __construct(string $type, array $srcset)
    {
        $this->type   = $type;
        $this->srcset = $srcset;
    }
}
