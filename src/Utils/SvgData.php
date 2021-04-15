<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class SvgData
{
    private string $viewbox;
    private string $def;
    private int $width;
    private int $height;

    public function __construct(string $filename)
    {
        $data = file_get_contents($filename);

        preg_match('/viewBox="(.*?)"/', $data, $matches);
        $this->viewbox = $matches[1] ?? '0 0 24 24';

        $from      = mb_strpos($data, '>') + 1;
        $this->def = mb_substr($data, $from);
        $this->def = trim(str_replace('</svg>', '', $this->def));

        $parts        = explode(' ', $this->viewbox);
        $this->width  = intval($parts[2]) - intval($parts[0]);
        $this->height = intval($parts[3]) - intval($parts[1]);
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
