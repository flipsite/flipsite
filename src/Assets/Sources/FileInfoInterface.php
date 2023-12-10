<?php

declare(strict_types=1);

namespace Flipsite\Assets\Sources;

interface FileInfoInterface
{
    public function getFilename(): string;
    public function getContents(): string;
    public function getSize(): int;
}
