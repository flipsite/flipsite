<?php

declare(strict_types=1);
namespace Flipsite\Assets;

use Flipsite\Assets\Sources\AssetSourcesInterface;
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
    private array $imageTypes = [
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg'  => 'image/svg+xml',
    ];

    private array $videoTypes = [
        'mp4'  => 'video/mp4',
        'mov'  => 'video/mov',
        'webm' => 'video/webm',
        'mov'  => 'video/mov',
    ];

    private array $fileTypes = [
        'csv'  => 'text/csv',
        'txt'  => 'text/plain',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
    ];

    public function __construct(protected AssetSourcesInterface $assetSources)
    {
    }

    public function getAssetSources(): AssetSourcesInterface
    {
        return $this->assetSources;
    }

    public function getResponse(Response $response, string $asset): Response
    {
        if ($this->assetSources->isOrginal($asset)) {
            return $this->assetSources->getResponse($response, $asset);
        }
        if ($this->assetSources->isCached($asset)) {
            return $this->assetSources->getResponse($response, $asset);
        }
        $pathinfo = pathinfo($asset);
        switch ($this->getType($pathinfo['extension'])) {
            case 'image':
                $withoutHash = preg_replace('/\.[a-f0-9]{6}\./', '.', $asset);
                if ('svg' !== $pathinfo['extension']) {
                    $tmp       = explode('@', $withoutHash);
                    $imageInfo = $this->assetSources->getImageInfo($tmp[0].'.'.$pathinfo['extension']);
                    $options   = new RasterOptions($asset);
                    if (!$imageInfo) {
                        throw new \Exception('No source found '.$asset);
                    }
                    $editor  = new RasterEditor($options, $imageInfo, $pathinfo['extension']);
                    $encoded = $editor->getImage();
                    $this->assetSources->addToCache($asset, (string)$encoded);
                } else {
                    $imageInfo = $this->assetSources->getImageInfo($withoutHash);
                    $this->assetSources->addToCache($asset, $imageInfo->getContents());
                }
                break;
            case 'video':
                $withoutHash = preg_replace('/\.[a-f0-9]{6}\./', '.', $asset);
                $videoInfo   = $this->assetSources->getVideoInfo($withoutHash);
                $this->assetSources->addToCache($asset, $videoInfo->getContents($pathinfo['extension']));
                break;
        }
        if ($this->assetSources->isCached($asset)) {
            return $this->assetSources->getResponse($response, $asset);
        }
        return $response->withStatus(404);
    }

    public function getType(string $extension) : ?string
    {
        if (isset($this->imageTypes[$extension])) {
            return 'image';
        }
        if (isset($this->videoTypes[$extension])) {
            return 'video';
        }
        if (isset($this->fileTypes[$extension])) {
            return 'file';
        }
        return null;
    }

    public function getMimetype(string $extension) : ?string
    {
        return $this->imageTypes[$extension] ?? $this->videoTypes[$extension] ?? $this->fileTypes[$extension] ?? null;
    }

    public function getSvg(string $svg): ?SvgInterface
    {
        $imageInfo = $this->assetSources->getImageInfo($svg);
        if ($imageInfo) {
            return new \Flipsite\Utils\SvgData($imageInfo->getContents());
        }
        return null;
    }

    public function getImageAttributes(string $image, array $options = [], ?ImageInfoInterface $imageInfo = null): ?ImageAttributesInterface
    {
        if (0 === mb_strpos($image, 'http')) {
            if (str_starts_with($image, 'https://images.unsplash.com')) {
                return new UnsplashAttributes($image, $options);
            }
            return new ExternalImageAttributes($image);
        }
        if (!$imageInfo) {
            $imageInfo = $this->assetSources->getImageInfo($image);
        }
        if ($imageInfo) {
            if (str_ends_with($image, '.svg')) {
                return new SvgAttributes($imageInfo, $this->assetSources);
            } else {
                return new ImageAttributes($options, $imageInfo, $this->assetSources);
            }
        }
        return null;
    }

    public function getVideoAttributes(string $video, ?VideoInfoInterface $videoInfo = null): ?VideoAttributesInterface
    {
        if (0 === mb_strpos($video, 'http')) {
            return new ExternalVideoAttributes($video);
        }
        if (!$videoInfo) {
            $videoInfo = $this->assetSources->getVideoInfo($video);
        }
        if ($videoInfo) {
            return new VideoAttributes($videoInfo, $this->assetSources);
        }
        return null;
    }

    public function upload(string $filename, string $filepath) : string|bool
    {
        $pathinfo = pathinfo($filename);
        $type     = $this->getType($pathinfo['extension']);
        if (!$type) {
            return false;
        }
        $mimetype = $this->getMimetype($pathinfo['extension']);
        if ($mimetype !== mime_content_type($filepath)) {
            return false;
        }
        $filename = $this->cleanUpFilename($filename);
        if ($this->fileExists($filename)) {
            return false;
        }
        return $this->assetSources->upload($type, $filename, $filepath) ? $filename : null;
    }

    public function rename(string $filename, string $newFilename) : string|bool
    {
        if (!$this->fileExists($filename)) {
            return false;
        }
        $newFilename = $this->cleanupFilename($newFilename);
        if ($this->fileExists($newFilename)) {
            return false;
        }
        $pathinfo = pathinfo($filename);
        $type     = $this->getType($pathinfo['extension']);
        if (!$type) {
            return false;
        }
        $pathinfoNew = pathinfo($newFilename);
        if (!isset($pathinfoNew['extension']) || $pathinfoNew['extension'] !== $pathinfo['extension']) {
            return false;
        }
        return $this->assetSources->rename($type, $filename, $newFilename) ? $newFilename : false;
    }

    public function delete(string $filename) : bool
    {
        $pathinfo = pathinfo($filename);
        $type = $this->getType($pathinfo['extension']);
        if (!$type) {
            return false;
        }
        if (!$this->fileExists($filename)) {
            return false;
        }
        return $this->assetSources->delete($type, $filename);
    }

    private function cleanupFilename(string $filename): string
    {
        $tmp      = explode('@', $filename);
        $filename = $tmp[0];

        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);

        // Remove all characters except letters, numbers, hyphens, underscores, and periods
        $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '', $filename);

        $filename = str_replace('.jpeg', '.jpg', $filename);

        return $filename;
    }

    private function fileExists(string $filename) : bool
    {
        return !!($this->assetSources->getImageInfo($filename) ?? $this->assetSources->getVideoInfo($filename) ?? $this->assetSources->getFileInfo($filename));
    }
}
