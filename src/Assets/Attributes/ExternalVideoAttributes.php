<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

class ExternalVideoAttributes implements VideoAttributesInterface
{
    public function __construct(private string $src)
    {
    }

    public function getSources() : array
    {
        $parts     = pathinfo($this->src);
        $sources[] = new SourceAttributes($this->src, $this->getVideoMimeTypeFromExtension($parts['extension'] ?? ''));
        return $sources;
    }

    private function getVideoMimeTypeFromExtension(string $extension)
    {
        $mimeTypes = [
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'mov'  => 'video/quicktime',
            'avi'  => 'video/x-msvideo',
            'mkv'  => 'video/x-matroska',
        ];

        if (array_key_exists($extension, $mimeTypes)) {
            return $mimeTypes[$extension];
        } else {
            return 'application/octet-stream'; // generic binary
        }
    }
}
