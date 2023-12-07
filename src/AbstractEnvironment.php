<?php

declare(strict_types=1);

namespace Flipsite;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Utils\Plugins;

abstract class AbstractEnvironment
{
    protected AssetSourcesInterface $assetSources;
    protected bool $isLive;
    protected string $scheme;
    protected string $host;
    protected ?string $port;
    protected string $basePath = '/'; //must start with /

    public function getAssetSources(): AssetSourcesInterface {
        return $this->assetSources;
    }
    
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getImgBasePath(): string
    {
        return $this->basePath . '/img';
    }

    public function getVideoBasePath(): string
    {
        return $this->basePath . '/videos';
    }

    public function getServer(bool $basePath = true): string
    {
        if ($basePath) {
            return trim($this->server . $this->basePath, '/');
        }
        return $this->server;
    }

    
    public function getExternalAssetDirs(): array
    {
        return $this->externalAssetDirs;
    }

    public function isLive(): bool
    {
        return $this->live;
    }

    public function hasTrailingSlash(): bool
    {
        return $this->trailingSlash;
    }

    public function optimizeHtml(): bool
    {
        return false;
    }

    public function optimizeCss(): bool
    {
        return false;
    }
    public function getGenerator(): ?string
    {
        return $this->generator;
    }

}
