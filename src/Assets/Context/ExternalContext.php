<?php

declare(strict_types=1);

namespace Flipsite\Assets\Context;

final class ExternalContext extends AbstractImageContext
{
    public function getSrc(): string
    {
        return $this->image;
    }

    public function getSources(): ?array
    {
        return null;
    }
}
