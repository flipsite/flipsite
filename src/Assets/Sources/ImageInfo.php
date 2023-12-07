<?php

declare(strict_types=1);

namespace Flipsite\Assets\Sources;

class ImageInfo implements ImageInfoInterface
{
    private string $filename;
    private string $hash;
    private ?int $width = null;
    private ?int $height = null;
    private string $filepath;

    public function __construct(string $dir, string $filename)
    {
        $this->filename = $filename;
        $this->filepath = $dir.'/'.$filename;
        $this->hash     = mb_substr(md5_file($this->filepath), 0, 6);
        $imageSize      = getimagesize($this->filepath);
        if ($imageSize) {
            $this->width = $imageSize[0];
            $this->height = $imageSize[1];
        }
    }

    public function getContents() : string {
        return file_get_contents($this->filepath);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }
}
