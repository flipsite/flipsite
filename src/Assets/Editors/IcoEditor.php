<?php

declare(strict_types=1);

namespace Flipsite\Assets\Editors;

final class IcoEditor extends AbstractImageEditor
{
    public function create() : void
    {
        $cachedFilename = $this->getCachedFilename();
        $this->fileSystem->copy($this->file->getFilename(), $cachedFilename);
        $ico = file_get_contents($this->file->getFilename());
        file_put_contents($cachedFilename, $ico);
    }
}
