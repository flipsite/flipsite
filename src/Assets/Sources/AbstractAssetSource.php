<?php

declare(strict_types=1);

namespace Flipsite\Assets\Sources;

use Flipsite\Exceptions\AssetSourcesNotInstalledException;

abstract class AbstractAssetSource
{
    protected string $packageDir;
    protected string $type;

    public function __construct(string $vendorDir, string $type)
    {
        $this->packageDir = $vendorDir.'/'.$this->package();
        $this->type       = $type;
    }

    abstract public function resolve(string $src) : ?string;

    public function isInstalled() : void
    {
        if (!is_dir($this->packageDir)) {
            throw new AssetSourcesNotInstalledException($this->type, $this->package());
        }
    }

    abstract protected function package() : string;
}
