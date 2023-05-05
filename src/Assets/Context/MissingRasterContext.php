<?php

declare(strict_types=1);
namespace Flipsite\Assets\Context;

final class MissingRasterContext extends AbstractImageContext
{
    public function __construct(string $image)
    {
        $this->image = $image;
    }

    public function getSrc() : string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAA1JREFUGFdjeO/i8h8ABhQCdy+wmG4AAAAASUVORK5CYII=';
    }

    public function getSrcset(?string $type = null) : ?string
    {
        return null;
    }
}
