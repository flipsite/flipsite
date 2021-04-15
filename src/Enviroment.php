<?php

declare(strict_types=1);

namespace Flipsite;

use Flipsite\Assets\Sources\AssetSources;
use Flipsite\Exceptions\EnviromentException;

final class Enviroment
{
    private bool $live;
    private string $siteDir;
    private string $vendorDir;
    private string $imgDir;
    private string $basePath;
    private string $server;
    private ?AssetSources $imageSources = null;

    public function __construct()
    {
        $this->live = 'live' === getenv('APP_ENV');

        if (false === getenv('IMG_DIR')) {
            throw new EnviromentException('IMG_DIR not set');
        }
        $this->imgDir = getenv('IMG_DIR');

        if (false === getenv('SITE_DIR')) {
            throw new EnviromentException('SITE_DIR not set');
        }
        $this->siteDir = getenv('SITE_DIR');

        $this->vendorDir = getenv('VENDOR_DIR');

        if (false === getenv('APP_BASEPATH')) {
            throw new EnviromentException('APP_BASEPATH not set');
        }
        $this->basePath = getenv('APP_BASEPATH');

        if (false === getenv('APP_SERVER')) {
            throw new EnviromentException('APP_SERVER not set');
        }
        $this->server = trim(getenv('APP_SERVER'), '/');
    }

    public function getImageSources() : AssetSources
    {
        if (null === $this->imageSources) {
            $this->imageSources = new AssetSources(
                $this->getVendorDir(),
                $this->getSiteDir()
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

    public function getServer(bool $basePath = true) : string
    {
        if ($basePath) {
            return trim($this->server.$this->basePath, '/');
        }
        return $this->server;
    }

    public function getImgDir() : string
    {
        return $this->imgDir;
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
