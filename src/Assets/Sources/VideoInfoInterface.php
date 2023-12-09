<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;

interface VideoInfoInterface
{
    public function getFilename() : string;

    public function getTypes() : array;

    public function getHash(string $type) : string;

    public function getContents(string $type) : string;
}
