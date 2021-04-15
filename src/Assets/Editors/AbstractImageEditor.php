<?php

declare(strict_types=1);

namespace Flipsite\Assets\Editors;

use Flipsite\Assets\ImageFile;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractImageEditor
{
    protected string $cacheDir;
    protected ImageFile $file;
    protected string $path;
    protected Filesystem $fileSystem;

    public function __construct(string $cacheDir, ImageFile $file, string $path)
    {
        $this->file       = $file;
        $this->path       = $path;
        $this->cacheDir   = $cacheDir;
        $this->fileSystem = new Filesystem();
        $this->fileSystem->mkdir($this->cacheDir, 0777);
    }

    abstract public function create() : void;

    protected function getCachedFilename() : string
    {
        return $this->cacheDir.'/'.$this->path;
    }
}
