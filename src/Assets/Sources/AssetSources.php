<?php

declare(strict_types=1);

namespace Flipsite\Assets\Sources;

final class AssetSources
{
    private array $videoFormats = ['ogg','webm','mp4'];

    public function __construct(private string $vendorDir, private array $assetDirs = [])
    {
        $this->assetDirs[] = $this->vendorDir.'/flipsite/flipsite/assets';
    }

    public function getFilename(string $src): ?string
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
        throw new \Flipsite\Exceptions\AssetNotFoundException($src);
    }
}
