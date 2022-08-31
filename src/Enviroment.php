<?php

declare(strict_types=1);

namespace Flipsite;

final class Enviroment extends AbstractEnviroment
{
    public function __construct()
    {
        $this->live = 'live' === getenv('APP_ENV');

        if (false === getenv('IMG_DIR')) {
            throw new EnviromentException('IMG_DIR not set');
        }
        $this->imgDir = getenv('IMG_DIR');

        if (false === getenv('VIDEO_DIR')) {
            throw new EnviromentException('VIDEO_DIR not set');
        }
        $this->videoDir = getenv('VIDEO_DIR');

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
 
        if (false !== getenv('ASSET_DIRS')) {
            $this->externalAssetDirsexplode = explode(',', getenv('ASSET_DIRS'));
        }
    }
}
