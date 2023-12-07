<?php

declare(strict_types=1);

namespace Flipsite\Assets\Sources;

use Psr\Http\Message\ResponseInterface as Response;

final class AssetSources implements AssetSourcesInterface
{
    private array $imageDirs = [];

    public function __construct(private string $vendorDir, private string $siteDir, private string $cacheDir, private string $basePath)
    {
        $this->imageDirs[] = $vendorDir.'/flipsite/flipsite/assets';
        $this->imageDirs[] = $siteDir.'/assets';
    }

    public function isCached(string $asset): bool
    {
        return file_exists($this->cacheDir.'/'.$asset);
    }
    public function getResponse(Response $response, string $asset): Response
    {
        $filename = $this->cacheDir.'/'.$asset;
        $body = $response->getBody();
        $body->rewind();
        $body->write(file_get_contents($filename));
        return $response->withHeader('Content-type', mime_content_type($filename));
    }

    public function getImageInfo(string $image): ?ImageInfoInterface
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

    public function addImageBasePath(string $image): string
    {
        return $this->basePath.'/img/'.$image;
    }

    public function addToCache(string $asset, string $encoded) : bool {
        return !!file_put_contents($this->cacheDir.'/'.$asset, $encoded);
    }
}

