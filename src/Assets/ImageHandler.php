<?php

declare(strict_types=1);

namespace Flipsite\Assets;

use Exception;
use Flipsite\Assets\Context\AbstractImageContext;
use Flipsite\Assets\Context\ExternalContext;
use Flipsite\Assets\Context\IcoContext;
use Flipsite\Assets\Context\RasterContext;
use Flipsite\Assets\Context\SvgContext;
use Flipsite\Assets\Editors\IcoEditor;
use Flipsite\Assets\Editors\RasterEditor;
use Flipsite\Assets\Editors\SvgEditor;
use Flipsite\Assets\Options\RasterOptions;
use Flipsite\Assets\Options\SvgOptions;
use Flipsite\Assets\Sources\AssetSources;
use Intervention\Image\ImageManager;
use Psr\Http\Message\ResponseInterface as Response;

final class ImageHandler
{
    private AssetSources $assetSources;
    private string $cacheDir;
    private bool $webp = true;

    public function __construct(AssetSources $assetSources, string $cacheDir)
    {
        $this->assetSources = $assetSources;
        $this->cacheDir     = $cacheDir;
        $this->webp         = (extension_loaded('gd') && function_exists('imagewebp')) || (function_exists('\Imagick::queryFormats') && \Imagick::queryFormats('WEBP'));
    }

    public function __invoke(string $src, ?array $args = null) : AbstractImageContext
    {
        return $this->getContext($src, $args);
    }

    public function getContext(string $src, ?array $args = null) : AbstractImageContext
    {
        if (0 === mb_strpos($src, 'http')) {
            return new ExternalContext($src);
        }
        $file = new ImageFile($src, $this->assetSources);
        if ('svg' === $file->getExtension()) {
            return new SvgContext($src, $file, new SvgOptions($args));
        }
        if ('ico' === $file->getExtension()) {
            return new IcoContext($src, $file);
        }
        return new RasterContext($src, $file, new RasterOptions($args), $args['srcset'] ?? null, $this->webp);
    }

    public function getResponse(Response $response, string $path) : Response
    {
        if ($this->inCache($path)) {
            return $this->getCached($response, $path);
        }
        try {
            $file = ImageFile::fromRequest($path, $this->assetSources);
        } catch (Exception $e) {
            return $this->notFound();
        }
        switch ($file->getExtension()) {
            case 'svg':
                $editor = new SvgEditor($this->cacheDir, $file, $path);
                break;
            case 'ico':
                $editor = new IcoEditor($this->cacheDir, $file, $path);
                break;
            default:
                $editor = new RasterEditor($this->cacheDir, $file, $path);
        }
        try {
            $editor->create();
        } catch (Exception $e) {
            return $this->notFound();
        }
        return $this->getCached($response, $path);
    }

    private function inCache(string $path) : bool
    {
        return file_exists($this->cacheDir.'/'.$path);
    }

    private function getCached(Response $response, string $path) : Response
    {
        $filename = $this->cacheDir.'/'.$path;
        $pathinfo = pathinfo($path);
        if ('svg' === $pathinfo['extension']) {
            $body = $response->getBody();
            $body->rewind();
            $body->write(file_get_contents($filename));
            return $response->withHeader('Content-type', 'image/svg+xml');
        }
        $manager = new ImageManager();
        $image   = $manager->make($filename);
        return $image->psrResponse();
    }

    private function notFound() : Response
    {
        $manager = new ImageManager();
        $image   = $manager->canvas(16, 9, '#eee');
        return $image->psrResponse();
    }
}
