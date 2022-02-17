<?php

declare(strict_types=1);

namespace Flipsite\Assets;

use Flipsite\Assets\Sources\AssetSources;

final class AssetFile
{
    private const START = '@';

    private string $filename;
    private string $extension;
    private string $src;

    public function __construct(string $src, AssetSources $assetSources)
    {
        $this->src       = $src;
        $filename        = $assetSources->getFilename($src);
        $this->filename  = $filename;
        $parts           = explode('.', $filename);
        $this->extension = end($parts);
    }

    public static function fromRequest(
        string $path,
        AssetSources $assetSources) : self
    {
        // Remove hash
        $parts = preg_split('/\.[0-9a-f]{6}\./', $path);
        if (1 === count($parts)) {
            $parts = explode('.', $parts[0]);
        }
        // Remove options
        $filename = explode(self::START, $parts[0]);
        return new self($filename[0].'.'.$parts[1], $assetSources);
    }

    public function getExtension() : string
    {
        return $this->extension;
    }

    public function getSrc() : string
    {
        return $this->src;
    }

    public function getFilename() : string
    {
        return $this->filename;
    }

    public function getHash() : string
    {
        return mb_substr(md5_file($this->filename), 0, 6);
    }
}
