<?php

declare(strict_types=1);

namespace Flipsite\Assets;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Attributes\ImageAttributesInterface;
use Flipsite\Assets\Attributes\UnsplashImageAttributes;
use Flipsite\Assets\Attributes\ExternalImageAttributes;
use Flipsite\Assets\Attributes\InternalImageAttributes;
use Flipsite\Assets\Options\RasterOptions;
use Flipsite\Assets\Editors\RasterEditor;
use Psr\Http\Message\ResponseInterface as Response;

class Assets
{
    public function __construct(protected AssetSourcesInterface $assetSources) {}

    public function getResponse(Response $response, string $asset): Response
    {
        if ($this->assetSources->isCached($asset)) {
            return $this->assetSources->getResponse($response, $asset);
        }
        $pathinfo = pathinfo($asset);
        switch ($pathinfo['extension']) {
            case 'webp':
            case 'gif':
            case 'png':
            case 'jpg':
            case 'jpeg':
                $tmp = explode('@', $pathinfo['filename']);
                $options  = new RasterOptions($asset);
                $imageInfo = $this->assetSources->getImageInfo($tmp[0].'.'.$pathinfo['extension']);
                if (!$imageInfo) {
                    throw new \Exception('No source found '.$asset);
                }
                $editor = new RasterEditor($options, $imageInfo, $pathinfo['extension']);
                $encoded = $editor->getImage();
                $this->assetSources->addToCache($asset, (string)$encoded);
        }
        return $this->assetSources->getResponse($response, $asset);
    }

    public function getSvg(string $filename): ?SvgInterface
    {
        return null;
    }

    public function getImageAttributes(string $image, array $options): ?ImageAttributesInterface
    {

        if (0 === mb_strpos($image, 'http')) {
            if (str_starts_with($image, 'https://images.unsplash.com')) {
                return new UnsplashImageAttributes($image, $options);
            }
            return new ExternalImageAttributes($image);
        }
        $imageInfo = $this->assetSources->getImageInfo($image);
        if ($imageInfo) {

            return new InternalImageAttributes($image, $options, $imageInfo, $this->assetSources);
        }
        return null;
    }
}
