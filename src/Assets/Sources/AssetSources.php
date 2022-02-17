<?php

declare(strict_types=1);

namespace Flipsite\Assets\Sources;

final class AssetSources
{
    private string $vendorDir;
    private string $siteDir;
    /**
     * @var array<AbstractAssetSource>
     */
    private array $sources = [];
    private array $videoFormats = ['ogg','webm','mp4'];

    public function __construct(string $vendorDir, string $siteDir)
    {
        $this->vendorDir = $vendorDir;
        $this->siteDir   = $siteDir;
    }

    public function getFilename(string $src) : ?string
    {
        $assetDirs = [
            $this->siteDir.'/assets/',
            $this->vendorDir.'/flipsite/flipsite/assets/',
        ];

        foreach ($assetDirs as $assetDir) {
            if (file_exists($assetDir.$src)) {
                return $assetDir.$src;
            }
            if (mb_strpos($src, '.webp')) {
                $filename = $assetDir.$src;
                foreach (['jpg', 'png'] as $ext) {
                    $alt = str_replace('.webp', '.'.$ext, $filename);
                    if (file_exists($alt)) {
                        return $alt;
                    }
                }
            }
        }


        $parts = explode('/', $src);
        $type  = $parts[0];
        $asset = str_replace($type.'/', '', $src);
        if (isset($this->sources[$type])) {
            return $this->sources[$type]->resolve($asset);
        }

        $class = str_replace(' ', '', ucwords(str_replace('-', ' ', ucfirst($type))));
        $class = 'Flipsite\Icons\\'.$class;
        if (class_exists($class)) {
            $source = new $class($this->vendorDir, $type);
            $source->isInstalled(); //throws exception if not installed
            $this->sources[$type] = $source;
            $filename             = $this->sources[$type]->resolve($asset);
            if (!file_exists($filename)) {
                throw new \Flipsite\Exceptions\AssetNotFoundException($asset, $filename);
            }
            return $filename;
        }
        throw new \Flipsite\Exceptions\AssetNotFoundException($asset, $src);
    }
}
