<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Image extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\EnviromentTrait;
    use Traits\ImageHandlerTrait;
    protected string $tag = 'img';
    protected bool $empty = true;
    protected bool $online = true;

    public function with(ComponentData $data) : void
    {
        $basePath = $this->enviroment->getBasePath();
        $src      = $data->get('value');
        $options  = $this->getOptions($data->getStyle('options'));

        $this->setAttribute('loading', $options['loading'] ?? 'lazy');
        $this->setAttribute('alt', $data->get('alt'));
        $this->addStyle($data->getStyle());

        if ($this->isSvg($src)) {
            $imageContext = $this->imageHandler->getContext($src, []);
        } else {
            $imageContext = $this->imageHandler->getContext($src, $options);
            $srcset = $imageContext->getSrcset('webp');
            foreach ($srcset as &$ss) {
                $ss->src = $basePath.'/img/'.$ss->src;
            }
            $this->setAttribute('src', $srcset[0]->src);
            $this->setAttribute('srcset', implode(', ', $srcset));
            if (isset($options['sizes'])) {
                $this->setAttribute('sizes', implode(', ', $options['sizes']));
            }
            $this->setAttribute('width', (string) $imageContext->getWidth());
            $this->setAttribute('height', (string) $imageContext->getHeight());
        }
        if (isset($options['loading']) && 'eager' === $options['loading']) {
            $this->builder->dispatch(new Event('preload', 'image', $this));
        }
    }

    private function isSvg(string $filename) : bool
    {
        return false !== mb_strpos($filename, '.svg');
    }

    private function getOptions(array $options) : array
    {
        if (!isset($options['sizes'])) {
            $options['sizes'] = ['100vw'];
            $options['width'] = 300;
        } else {
            $srcset           = [];
            $options['sizes'] = $this->calcSizes($options['sizes'], $srcset);
            if (!isset($options['srcset'])) {
                $options['srcset'] = $srcset;
                $options['width']  = intval($options['srcset'][0]);
            }
        }

        if (!isset($options['width'])) {
            $options['width'] = intval($options['srcset'][0]);
        }
        if (isset($options['aspectRatio'])) {
            $ratio = $this->getRatio($options['aspectRatio']);
            if (!isset($options['width'])) {
                $options['width'] = $options['height'] * $ratio;
            }
            if (!isset($options['height'])) {
                $options['height'] = $options['width'] / $ratio;
            }
        }
        return $options;
    }

    private function getRatio(string $aspectRatio) : ?float
    {
        $parts = explode('by', $aspectRatio);
        if (2 !== count($parts)) {
            return null;
        }
        return floatval($parts[0] / $parts[1]);
    }

    private function calcSizes(array $sizes, array &$srcset, array $multiplier = [1, 2, 3]) : array
    {
        foreach ($sizes as $size) {
            $tmp = array_reverse(explode(') ', $size));
            $w   = array_shift($tmp);
            if (mb_strpos($w, 'px')) {
                $w = intval($w);
                foreach ($multiplier as $m) {
                    $srcset[] = $w * $m;
                }
            }
        }
        $srcset = array_unique($srcset);
        sort($srcset);
        foreach ($srcset as &$s) {
            $s .= 'w';
        }
        return $sizes;
    }
}
