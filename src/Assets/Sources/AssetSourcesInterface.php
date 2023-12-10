<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;
use Psr\Http\Message\ResponseInterface as Response;

interface AssetSourcesInterface
{
    public function getImagesList() : array;
    public function getVideosList() : array;
    public function getFilesList() : array;
    public function getImageInfo(string $image) : ?ImageInfoInterface;
    public function getVideoInfo(string $video) : ?VideoInfoInterface;
    public function getFileInfo(string $file) : ?FileInfoInterface;
    public function addImageBasePath(string $image) : string;
    public function addVideoBasePath(string $video) : string;
    public function addFileBasePath(string $file) : string;
    public function isCached(string $asset) : bool;
    public function addToCache(string $asset, string $encoded) : bool;
    public function getResponse(Response $response, string $asset) : Response;
}
