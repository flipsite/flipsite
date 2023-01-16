<?php

declare(strict_types=1);
namespace Flipsite;

use Flipsite\Assets\Sources\AssetSources;

abstract class AbstractEnvironment
{
    protected bool $live;
    protected string $siteDir;
    protected string $vendorDir;
    protected string $imgDir;
    protected string $basePath;
    protected string $server;
    protected array $externalAssetDirs    = [];
    protected ?AssetSources $imageSources = null;
    protected ?AssetSources $videoSources = null;

    public function getAssetSources() : AssetSources
    {
        if (null === $this->imageSources) {
            $assetDirs = [$this->getSiteDir().'/assets'];
            if (count($this->externalAssetDirs)) {
                $assetDirs = array_merge($assetDirs, $this->externalAssetDirs);
            }
            $this->imageSources = new AssetSources(
                $this->getVendorDir(),
                $assetDirs
            );
        }
        return $this->imageSources;
    }

    public function getVendorDir() : string
    {
        return $this->vendorDir;
    }

    public function getBasePath() : string
    {
        return $this->basePath;
    }

    public function getImgBasePath() : string
    {
        return $this->basePath . '/img';
    }

    public function getVideoBasePath() : string
    {
        return $this->basePath . '/videos';
    }

    public function getServer(bool $basePath = true) : string
    {
        if ($basePath) {
            return trim($this->server . $this->basePath, '/');
        }
        return $this->server;
    }

    public function getImgDir() : string
    {
        return $this->imgDir;
    }

    public function getVideoDir() : string
    {
        return $this->videoDir;
    }

    public function getSiteDir() : string
    {
        return $this->siteDir;
    }

    public function isLive() : bool
    {
        return $this->live;
    }
}
