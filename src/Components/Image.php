<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Image extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\EnviromentTrait;
    use Traits\ImageHandlerTrait;
    use Traits\ReaderTrait;

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
        $multipliers = [1,2];
        //TODO
        $screens = [
            'xs' => 440,
            'sm' =>  640,
            'md' =>  768,
            'lg' =>  1024,
            'xl' =>  1280,
            '2xl' =>  1536,
        ];

        $srcset = [];
        foreach ($options['srcset'] as $ss) {
            $ss = (string)$ss;
            $divideBy = 1;
            if (strpos($ss, '/')) {
                $tmp = explode('/', $ss);
                $divideBy = $tmp[1];
                $ss = $tmp[0];
            }
            if (isset($screens[$ss])) {
                $ss = $screens[$ss];
            }
            if ($divideBy !== 1) {
                $ss = intval(floatVal($ss)/floatVal($divideBy));
            }
            $srcset[] = $ss;
        }
        sort($srcset);
        $options['srcset'] = array_unique($srcset);

        foreach ($options['sizes'] as &$size) {
            $screen = null;
            $size = explode(':', $size);
            if (count($size) === 2) {
                $screen = $screens[array_shift($size)];
            }

            $divideBy = 1;
            $size = explode('/', $size[0]);
            if (count($size) === 2) {
                $divideBy = intval(array_pop($size));
            }
            $size = $size[0];
            if (isset($screens[$size])) {
                $size = $screens[$size];
            }
            if ($divideBy !== 1) {
                $size = intval(floatVal($size)/floatVal($divideBy));
            }
            if (strpos((string)$size, 'vw') === false) {
                $size.='px';
            }
            if (null !== $screen) {
                $size = '(min-width:'.$screen.'px) '.$size;
            }
        }



        // if (!isset($options['sizes'])) {
        //     $options['sizes'] = ['100vw'];
        //     $options['width'] = 300;
        // } else {
        //     $srcset           = [];
        //     $options['sizes'] = $this->calcSizes($options['sizes'], $srcset);
        //     if (!isset($options['srcset'])) {
        //         $options['srcset'] = $srcset;
        //         $options['width']  = intval($options['srcset'][0]);
        //     }
        // }

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
        foreach ($options['srcset'] as &$ss) {
            $ss.='w';
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
