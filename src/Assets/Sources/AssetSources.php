<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;

use Flipsite\Utils\Plugins;

final class AssetSources implements AssetSourcesInterface
{
    private array $imageDirs = [];

    public function __construct(private string $vendorDir, private string $siteDir)
    {
        $this->imageDirs[] = $vendorDir.'/flipsite/flipsite/assets';
        $this->imageDirs[] = $siteDir.'/assets';
    }

    // public function getFilename(string $src, bool $runPlugins = true): ?string
    // {
    //     foreach ($this->assetDirs as $assetDir) {
    //         if (file_exists($assetDir.'/'.$src)) {
    //             return $assetDir.'/'.$src;
    //         }
    //         if (mb_strpos($src, '.webp')) {
    //             $filename = $assetDir.'/'.$src;
    //             foreach (['jpg', 'png'] as $ext) {
    //                 $alt = str_replace('.webp', '.'.$ext, $filename);
    //                 if (file_exists($alt)) {
    //                     return $alt;
    //                 }
    //             }
    //         }
    //     }
    //     if ($runPlugins && $this->plugins->has('assetNotFound')) {
    //         $this->plugins->run('assetNotFound', $src);
    //         return $this->getFilename($src, false);
    //     }
    //     throw new \Flipsite\Exceptions\AssetNotFoundException($src);
    // }
    public function getImageInfo(string $image) : ?ImageInfoInterface
    {
        foreach ($this->imageDirs as $imageDir) {
            if (file_exists($imageDir.'/'.$image)) {
                return new ImageInfo($imageDir, $image);
            }
            if (mb_strpos($image, '.webp')) {
                foreach (['jpg', 'png'] as $ext) {
                    $alt = str_replace('.webp', '.'.$ext, $image);
                    if (file_exists($imageDir.'/'.$alt)) {
                        return new ImageInfo($imageDir, $alt);
                    }
                }
            }
        }
        return null;
    }

    public function addImageBasePath(string $image) : string
    {
        return '/img/'.$image;
    }
}

class ImageInfo implements ImageInfoInterface
{
    private string $filename;
    private string $hash;
    private ?int $width;
    private ?int $height;

    public function __construct(string $dir, string $filename)
    {
        $this->filename = $filename;
        $this->hash     = mb_substr(md5_file($dir.'/'.$filename), 0, 6);
        $imageSize      = getimagesize($dir.'/'.$filename);
        $this->width    = $imageSize[0];
        $this->height   = $imageSize[1];
    }

    public function getFilename() : string
    {
        return $this->filename;
    }

    public function getHash() : string
    {
        return $this->hash;
    }

    public function getWidth() : ?int
    {
        return $this->width;
    }

    public function getHeight() : ?int
    {
        return $this->height;
    }
}
