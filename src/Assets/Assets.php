<?php

declare(strict_types=1);
namespace Flipsite\Assets;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Sources\AbstractAssetInfo;
use Flipsite\Assets\Sources\AssetType;
use Flipsite\Assets\Attributes\ImageAttributesInterface;
use Flipsite\Assets\Attributes\UnsplashAttributes;
use Flipsite\Assets\Attributes\ImageAttributes;
use Flipsite\Assets\Attributes\SvgAttributes;
use Flipsite\Assets\Attributes\VideoAttributesInterface;
use Flipsite\Assets\Attributes\VideoAttributes;
use Flipsite\Assets\Attributes\ExternalVideoAttributes;
use Flipsite\Assets\Options\RasterOptions;
use Flipsite\Assets\Editors\RasterEditor;
use Psr\Http\Message\ResponseInterface as Response;

class Assets
{
    public function __construct(protected AssetSourcesInterface $assetSources)
    {
    }

    public function getAssetSources(): AssetSourcesInterface
    {
        return $this->assetSources;
    }

    public function getContents(string $asset): string|bool {
        $assetInfo = $this->assetSources->getInfo($asset);
        if ($assetInfo) {
            return $assetInfo->getContents();
        }

        if ($this->assetSources->isCached($asset)) {
            return $this->assetSources->getCached($asset);
        }
        $this->addToCache($asset);
        if ($this->assetSources->isCached($asset)) {
            return $this->assetSources->getCached($asset);
        }
        return false;
    }

    public function getResponse(Response $response, string $asset): Response
    {
        if ($this->assetSources->isOrginal($asset)) {
            return $this->assetSources->getResponse($response, $asset);
        }
        if ($this->assetSources->isCached($asset)) {
            return $this->assetSources->getResponse($response, $asset);
        }
        $this->addToCache($asset);
        
        if ($this->assetSources->isCached($asset)) {
            return $this->assetSources->getResponse($response, $asset);
        }
        return $response->withStatus(404);
    }

    private function addToCache(string $asset) {
        $pathinfo = pathinfo($asset);
        $withoutHash = preg_replace('/\.[a-f0-9]{6}/', '', $pathinfo['filename']);
        $tmp = explode('@', $withoutHash);
        $filename = $tmp[0];

        $assetInfo = $this->assetSources->getInfo($filename.'.'.$pathinfo['extension']);
        
        switch ($assetInfo->getType()) {
            case AssetType::IMAGE:
                if ('svg' !== $pathinfo['extension']) {
                    $options   = new RasterOptions($asset);
                    if (!$assetInfo) {
                        throw new \Exception('No source found '.$asset);
                    }
                    $editor  = new RasterEditor($options, $assetInfo, $pathinfo['extension']);
                    $encoded = $editor->getImage();
                    $this->assetSources->addToCache($asset, (string)$encoded);
                } else {
                    $this->assetSources->addToCache($asset, $assetInfo->getContents());
                }
                break;
            case AssetType::VIDEO:
                $this->assetSources->addToCache($asset, $assetInfo->getContents());
                break;
        }

    }

    public function getSvg(string $svg): ?SvgInterface
    {
        $assetInfo = $this->assetSources->getInfo($svg);
        if ($assetInfo) {
            return new \Flipsite\Utils\SvgData($assetInfo->getContents());
        }
        return null;
    }

    public function getImageAttributes(string $image, array $options = [], ?AbstractAssetInfo $assetInfo = null): ?ImageAttributesInterface
    {
        if (0 === mb_strpos($image, 'http')) {
            if (str_starts_with($image, 'https://images.unsplash.com')) {
                return new UnsplashAttributes($image, $options);
            }
            return new ExternalImageAttributes($image);
        }
        if (!$assetInfo) {
            $assetInfo = $this->assetSources->getInfo($image);
        }
        if ($assetInfo) {
            if (str_ends_with($image, '.svg')) {
                return new SvgAttributes($assetInfo, $this->assetSources);
            } else {
                return new ImageAttributes($options, $assetInfo, $this->assetSources);
            }
        }
        return null;
    }

    public function getVideoAttributes(string $video, ?AbstractAssetInfo $assetInfo = null): ?VideoAttributesInterface
    {
        if (0 === mb_strpos($video, 'http')) {
            return new ExternalVideoAttributes($video);
        }
        if (!$assetInfo) {
            $assetInfo = $this->assetSources->getInfo($video);
        }
        if ($assetInfo) {
            return new VideoAttributes($assetInfo, $this->assetSources);
        }
        return null;
    }

    public function upload(string $asset, string $filepath) : string|bool
    {
        $assetInfo = $this->assetSources->getInfo($asset);
        if ($assetInfo) {
            return false;
        }
        
        $asset = $this->cleanupAssetFilename($asset);
        
        if ($this->assetExists($asset)) {
            return false;
        }
        
        return $this->assetSources->upload(AssetType::fromMimetype(mime_content_type($filepath)), $asset, $filepath) ? $asset : null;
    }

    public function rename(string $asset, string $newFilename) : string|bool
    {
        $assetInfo = $this->assetSources->getInfo($asset);
        if (!$assetInfo) {
            return false;
        }
        $newAssetInfo = $this->assetSources->getInfo($newFilename);
        if ($newAssetInfo) {
            return false;
        }

        $pathinfo = pathinfo($asset);
        $pathinfoNew = pathinfo($newFilename);
        if (!isset($pathinfoNew['extension']) || $pathinfoNew['extension'] !== $pathinfo['extension']) {
            return false;
        }

        return $this->assetSources->rename($assetInfo->getType(), $asset, $newFilename) ? $newFilename : false;
    }

    public function delete(string $asset) : bool
    {
        $assetInfo = $this->assetSources->getInfo($asset);
        if (!$assetInfo) {
            return false;
        }
        return $this->assetSources->delete($assetInfo->getType(), $asset);
    }

    private function cleanupAssetFilename(string $asset): string
    {
        $tmp      = explode('@', $asset);
        $asset = $tmp[0];

        // Replace spaces with underscores
        $asset = str_replace(' ', '_', $asset);

        // Remove all characters except letters, numbers, hyphens, underscores, and periods
        $asset = preg_replace('/[^A-Za-z0-9\-_.]/', '', $asset);

        $asset = str_replace('.jpeg', '.jpg', $asset);

        return $asset;
    }

    private function assetExists(string $asset) : bool
    {
        return !!($this->assetSources->getInfo($asset));
    }
}
