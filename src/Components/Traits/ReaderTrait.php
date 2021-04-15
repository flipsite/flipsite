<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Data\Reader;

trait ReaderTrait
{
    protected Reader $reader;

    public function addReader(Reader $reader) : void
    {
        $this->reader = $reader;
    }
}
