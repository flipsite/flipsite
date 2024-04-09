<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use Flipsite\Assets\SvgInterface;

final class SvgData implements SvgInterface
{
    private string $viewbox;
    private string $def = '';
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

        $xml = simplexml_load_string($data);
        if ('svg' !== $xml->getName()) {
            throw new \Exception('Not valid SVG');
        }

        if (isset($xml['viewBox'])) {
            $this->viewbox = (string)$xml['viewBox'];
        }
        if (isset($xml['width']) && $xml['height']) {
            $this->width  = intval($xml['width']);
            $this->height = intval($xml['height']);
        }
        if (!isset($this->viewbox) && isset($this->width) && isset($this->height)) {
            $this->viewbox = '0 0 '.(string)$this->width.' '.(string)$this->height;
        }
        if (isset($this->viewbox) && !isset($this->width) && !isset($this->height)) {
            $parts        = explode(' ', $this->viewbox);
            $this->width  = intval($parts[2]) - intval($parts[0]);
            $this->height = intval($parts[3]) - intval($parts[1]);
        }
        if (!isset($this->viewbox) || !isset($this->width) || !isset($this->height)) {
            throw new \Exception('No SVG dimensions');
        }
        foreach ($xml->children() as $child) {
            if ('title' !== $child->getName()) {
                $this->def .= $child->asXML();
            }
        }
        $this->def = str_replace("\n", '', $this->def);
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
