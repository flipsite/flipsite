<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;
use Psr\Http\Message\ResponseInterface as Response;

interface AssetSourcesInterface
{
    public function getImageInfo(string $image) : ?ImageInfoInterface;
    public function addImageBasePath(string $image) : string;
    public function isCached(string $asset) : bool;
    public function addToCache(string $asset, string $encoded) : bool;
    public function getResponse(Response $response, string $asset) : Response;
}
