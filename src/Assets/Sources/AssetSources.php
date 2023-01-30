<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;

use Flipsite\Utils\Plugins;

final class AssetSources
{
    private array $videoFormats = ['ogg', 'webm', 'mp4'];

    public function __construct(private string $vendorDir, private Plugins $plugins, private array $assetDirs = [])
    {
        $this->assetDirs[] = $this->vendorDir.'/flipsite/flipsite/assets';
    }

    public function getFilename(string $src, bool $runPlugins = true): ?string
    {
        foreach ($this->assetDirs as $assetDir) {
            if (file_exists($assetDir.'/'.$src)) {
                return $assetDir.'/'.$src;
            }
            if (mb_strpos($src, '.webp')) {
                $filename = $assetDir.'/'.$src;
                foreach (['jpg', 'png'] as $ext) {
                    $alt = str_replace('.webp', '.'.$ext, $filename);
                    if (file_exists($alt)) {
                        return $alt;
                    }
                }
            }
        }
        if ($runPlugins && $this->plugins->has('assetNotFound')) {
            $this->plugins->run('assetNotFound', $src);
            $this->getFilename($src, false);
        }
        throw new \Flipsite\Exceptions\AssetNotFoundException($src);
    }
}
