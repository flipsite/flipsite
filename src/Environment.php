<?php

declare(strict_types=1);
namespace Flipsite;

use Flipsite\Assets\Sources\AssetSources;

final class Environment extends AbstractEnvironment
{
    public function __construct()
    {
        $this->assetSources = new AssetSources(
            getenv('VENDOR_DIR'), 
            getenv('SITE_DIR'),
            getenv('CACHE_DIR'),
            getenv('APP_BASEPATH')
        );

        $this->live = 'live' === getenv('APP_ENV');

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
