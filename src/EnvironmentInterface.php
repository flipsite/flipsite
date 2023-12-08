<?php

declare(strict_types=1);

namespace Flipsite;

use Flipsite\Assets\Sources\AssetSourcesInterface;

interface EnvironmentInterface {
    public function getAssetSources(): AssetSourcesInterface;
    public function getBasePath() : string;
    public function getGenerator() : ?string;
    public function getAbsoluteUrl(string $url) : string;
    public function getAbsoluteSrc(string $src) : string;
    public function minimizeHtml() : bool;
}
