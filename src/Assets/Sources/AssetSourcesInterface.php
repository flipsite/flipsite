<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;
use Psr\Http\Message\ResponseInterface as Response;

interface AssetSourcesInterface
{
    public function getList() : array;
    public function getInfo(string $asset) : ?AbstractAssetInfo;
    public function addBasePath(AssetType $type, string $asset) : string;
    public function isOrginal(string $asset) : bool;
    public function isCached(string $asset) : bool;
    public function addToCache(string $asset, string $encoded) : bool;
    public function getResponse(Response $response, string $asset) : Response;
    public function upload(AssetType $type, string $filename, string $filepath) : bool|string;
    public function rename(AssetType $type, string $filename, string $newFilename) : bool|string;
    public function delete(AssetType $type, string $filename) : bool;
}
