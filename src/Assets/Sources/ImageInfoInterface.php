<?php

declare(strict_types=1);

namespace Flipsite\Assets\Sources;

interface ImageInfoInterface
{
    public function getFilename() : string;
    public function getHash() : string;
    public function getWidth() : ?int;
    public function getHeight() : ?int;
}
