<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;

abstract class AbstractAssetInfo
{
    protected AssetType $type;
    protected string $filename;
    protected string $extension;
    protected int $size;
    protected string $mimetype;
    protected ?int $height = null;
    protected ?int $width  = null;

    public function getType(): AssetType
    {
        return $this->type;
    }

    public function getFilename(bool $withExtension = true): string
    {
        if (!$withExtension) {
            return str_replace('.'.$this->extension, '', $this->filename);
        } else {
            return $this->filename;
        }
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getMimetype(): string
    {
        return $this->mimetype;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    abstract public function getHash() : string;

    abstract public function getContents() : string;
}
