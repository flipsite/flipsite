<?php

declare(strict_types=1);

namespace Flipsite\Assets;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Attributes\ImageAttributesInterface;
use Flipsite\Assets\Attributes\UnsplashAttributes;
use Flipsite\Assets\Attributes\ExternalAttributes;
use Flipsite\Assets\Attributes\ImageAttributes;
use Flipsite\Assets\Attributes\SvgAttributes;
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
            case 'svg':
                $withoutHash = preg_replace('/\.[a-f0-9]{6}\./', '.', $asset);
                if ('svg' !== $pathinfo['extension']) {
                    $tmp = explode('@', $withoutHash);
                    $imageInfo = $this->assetSources->getImageInfo($tmp[0].'.'.$pathinfo['extension']);
                    $options  = new RasterOptions($asset);
                    if (!$imageInfo) {
                        throw new \Exception('No source found '.$asset);
                    }
                    $editor = new RasterEditor($options, $imageInfo, $pathinfo['extension']);
                    $encoded = $editor->getImage();
                    $this->assetSources->addToCache($asset, (string)$encoded);
                } else {
                    $imageInfo = $this->assetSources->getImageInfo($withoutHash);
                    $this->assetSources->addToCache($asset, $imageInfo->getContents());
                }
                break;
        }
        return $this->assetSources->getResponse($response, $asset);
    }

    public function getSvg(string $svg): ?SvgInterface
    {
        $imageInfo = $this->assetSources->getImageInfo($svg);
        if ($imageInfo) {
            return new \Flipsite\Utils\SvgData($imageInfo->getContents());
        }
        return null;
    }

    public function getImageAttributes(string $image, array $options): ?ImageAttributesInterface
    {

        if (0 === mb_strpos($image, 'http')) {
            if (str_starts_with($image, 'https://images.unsplash.com')) {
                return new UnsplashAttributes($image, $options);
            }
            return new ExternalImageAttributes($image);
        }
        $imageInfo = $this->assetSources->getImageInfo($image);
        if ($imageInfo) {
            if (str_ends_with($image,'.svg')) {
                return new SvgAttributes($imageInfo, $this->assetSources);
            } else {
                return new ImageAttributes($options, $imageInfo, $this->assetSources);
            }
        }
        return null;
    }
}
