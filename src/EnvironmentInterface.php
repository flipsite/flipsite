<?php

declare(strict_types=1);
namespace Flipsite;

use Flipsite\Assets\Sources\AssetSourcesInterface;

interface EnvironmentInterface
{
    public function getAssetSources(): AssetSourcesInterface;

    public function getBasePath(): string;

    public function getAssetsBasePath(): string;

    public function getGenerator(): ?string;

    public function getUrl(string $url): string;

    public function getAbsoluteUrl(string $url): string;

    public function getAbsoluteSrc(string $src): string;

    public function isProduction(): bool;

    public function minimizeHtml(): bool;

    public function minimizeCss(): bool;

    public function compileTimestamp(): bool;

    public function watermark(): bool;

    public function downloadFonts(): bool;
}
