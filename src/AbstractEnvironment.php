<?php

declare(strict_types=1);

namespace Flipsite;

use Flipsite\Assets\Sources\AssetSources;
use Flipsite\Utils\Plugins;

abstract class AbstractEnvironment
{
    protected bool $live;
    protected string $siteDir;
    protected string $vendorDir;
    protected string $imgDir;
    protected string $videoDir;
    protected string $basePath;
    protected string $server;
    protected ?string $generator = 'flipsite.io';
    protected bool $trailingSlash = false;
    protected array $externalAssetDirs    = [];
    protected ?AssetSources $imageSources = null;

    public function __construct(protected Plugins $plugins) {}

    public function getPlugins(): Plugins
    {
        return $this->plugins;
    }

    public function getAssetSources(): AssetSources
    {
        if (null === $this->imageSources) {
            $assetDirs = [$this->getSiteDir().'/assets'];
            if (count($this->externalAssetDirs)) {
                $assetDirs = array_merge($assetDirs, $this->externalAssetDirs);
            }
            $this->imageSources = new AssetSources(
                $this->getVendorDir(),
                $this->plugins,
                $assetDirs,
            );
        }
        return $this->imageSources;
    }

    public function getVendorDir(): string
    {
        return $this->vendorDir;
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

    public function getImgDir(): string
    {
        return $this->imgDir;
    }

    public function getVideoDir(): string
    {
        return $this->videoDir;
    }
    public function getExternalAssetDirs(): array
    {
        return $this->externalAssetDirs;
    }

    public function getSiteDir(): string
    {
        return $this->siteDir;
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
