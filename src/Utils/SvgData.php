<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use Flipsite\Assets\SvgInterface;

final class SvgData implements SvgInterface
{
    private string $viewbox;
    private string $def;
    private int $width;
    private int $height;

    public function __construct(string $data)
    {
        if (file_exists($data)) {
            $this->hash = substr(md5($data), 0, 6);
            $data       = file_get_contents($data);
            if (mime_content_type($data) !== 'image/svg+xml') {
                throw new \Exception('File is not a valid SVG');
            }
        } else {
            $this->hash = substr(md5($data), 0, 6);
        }

        $svgTagPos = strpos($data, '<svg');
        if (false === $svgTagPos) {
            throw new \Exception('Not valid SVG');
        }
        $data      = substr($data, $svgTagPos);

        preg_match('/viewBox="(.*?)"/', $data, $matches);
        $this->viewbox = $matches[1] ?? '0 0 24 24';

        $from      = mb_strpos($data, '>') + 1;
        $this->def = mb_substr($data, $from);

        $this->def    = trim(str_replace('</svg>', '', $this->def));
        $this->def    = str_replace("\n", ' ', $this->def);
        $this->def    = str_replace('> <', '><', $this->def);
        $this->def    = preg_replace('/<title>.*?<\/title>/', '', $this->def);
        $parts        = explode(' ', $this->viewbox);
        $this->width  = intval($parts[2]) - intval($parts[0]);
        $this->height = intval($parts[3]) - intval($parts[1]);
    }

    public function getHash() : string
    {
        return $this->hash;
    }

    public function getViewbox() : string
    {
        return $this->viewbox;
    }

    public function getDef() : string
    {
        return $this->def;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }
}
