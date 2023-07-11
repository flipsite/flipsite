<?php

declare(strict_types=1);

namespace Flipsite;

use Flipsite\Utils\Plugins;

final class Environment extends AbstractEnvironment
{
    public function __construct(Plugins $plugins)
    {
        parent::__construct($plugins);

        $this->live = 'live' === getenv('APP_ENV');

        if (false === getenv('IMG_DIR')) {
            throw new \Exception('IMG_DIR not set');
        }
        $this->imgDir = getenv('IMG_DIR');

        if (false === getenv('VIDEO_DIR')) {
            throw new \Exception('VIDEO_DIR not set');
        }
        $this->videoDir = getenv('VIDEO_DIR');

        if (false === getenv('SITE_DIR')) {
            throw new \Exception('SITE_DIR not set');
        }
        $this->siteDir = getenv('SITE_DIR');

        $this->vendorDir = getenv('VENDOR_DIR');

        if (false === getenv('APP_BASEPATH')) {
            throw new \Exception('APP_BASEPATH not set');
        }
        $this->basePath = getenv('APP_BASEPATH');

        if (false === getenv('APP_SERVER')) {
            throw new \Exception('APP_SERVER not set');
        }
        $this->server = trim(getenv('APP_SERVER'), '/');

        if (false !== getenv('ASSET_DIRS')) {
            $this->externalAssetDirs = explode(',', getenv('ASSET_DIRS'));
        }

        if (false !== getenv('TRAILING_SLASH')) {
            $this->trailingSlash = true;
        }
    }
}
