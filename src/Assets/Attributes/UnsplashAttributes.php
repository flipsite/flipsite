<?php

declare(strict_types=1);

namespace Flipsite\Assets\Attributes;

class UnsplashAttributes extends AbstractImageAttributes
{
    private string $srcTpl;

    public function __construct(string $src, array $options)
    {
        $url = parse_url($src);
        $parts = explode('&', $url['query']);
        $query = [];
        foreach ($parts as $param => $value) {
            $pair = explode('=', $value);
            $query[$pair[0]] = $pair[1];
        }
        $this->setSize($options, intval($query['w']), intval($query['h']));
        unset($query['w'],$query['h'],$query['color'],$query['blur_hash'],$query['user'],$query['name']);


        $this->srcset = $options['srcset'] ?? [];

        $this->srcTpl = $url['scheme'].'://'.$url['host'].$url['path'].'?';
        $first = true;
        foreach ($query as $param => $value) {
            if ($first) {
                $first = false;
                $this->srcTpl .= '&';
            }
            $this->srcTpl .= $param.'='.$value;
        }

        if (isset($options['aspectRatio'])) {
            $this->srcTpl .= '&fit=crop';
        }
        $this->srcTpl .= '&auto=format';

        $this->src = $this->buildSrc($this->width, $this->height);
    }

    public function getSrcset(?string $type = null): ?string
    {
        $srcset = [];
        foreach ($this->srcset as $variant) {
            preg_match('/[0-9\.]+[w|x]/', $variant, $matches);
            if (0 === count($matches)) {
                throw new \Exception('Invalid srcset variant (' . $variant . '). Should be multiplier (1x, 1.5x) or width (100w, 300w)');
            }
            if (false !== mb_strpos($variant, 'x')) {
                $factor = floatval(trim($variant, 'x'));
                $srcset[] = new ImageSrcset(
                    $this->buildSrc(intval($this->width * $factor), intval($this->height * $factor)),
                    $variant,
                    $type
                );
            } else {
                // TODO
                // $width = floatval(trim($variant, 'w'));
                // $scale = $width / floatval($this->options->getValue('width'));
                // $this->options->changeScale($scale);
            }
        }
        if (!count($srcset)) {
            return null;
        }
        return implode(', ', $srcset);
    }
    private function buildSrc(int $width, int $height)
    {
        $src = $this->srcTpl;
        $src .= '&w='.$width;
        $src .= '&h='.$height;
        return $src;

    }
}
