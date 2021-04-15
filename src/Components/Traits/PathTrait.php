<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Utils\Path;

trait PathTrait
{
    protected Path $path;

    public function addPath(Path $path) : void
    {
        $this->path = $path;
    }
}
