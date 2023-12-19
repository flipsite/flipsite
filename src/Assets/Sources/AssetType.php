<?php

declare(strict_types=1);
namespace Flipsite\Assets\Sources;

enum AssetType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case FILE = 'file';

    public static function fromMimetype(string $mimetype): static
    {
        $tmp = explode('/',$mimetype);
        switch ($tmp[0]) {
            case 'image': return static::IMAGE;
            case 'video': return static::VIDEO;
            default: return static::FILE;   
        }
    }
}