<?php

declare(strict_types=1);

namespace Flipsite\Assets\Context;

use Flipsite\Assets\ImageFile;

final class IcoContext extends AbstractImageContext
{
    private string $hash;

    public function __construct(string $src, ImageFile $file)
    {
        $this->src  = $src;
        $this->hash = $file->getHash();
    }

    public function getSrc() : string
    {
        $replace = $this->options.'.'.$this->hash.'.ico';
        return str_replace('.ico', $replace, $this->src);
    }

    public function getSources() : ?array
    {
        return null;
    }
}
