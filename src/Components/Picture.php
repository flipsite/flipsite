<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Picture extends AbstractComponent
{
    use Traits\EnviromentTrait;
    use Traits\ImageHandlerTrait;
    protected string $type = 'picture';

    public function build(array $data, array $style, array $flags) : void
    {
        if (isset($data['style'])) {
            $style = ArrayHelper::merge($style ?? [], $data['style']);
        }
        $basePath = $this->enviroment->getBasePath();
        if ($this->isSvg($data['value'])) {
            $imageContext = $this->imageHandler->getContext($data['value'], []);
            $options      = $style['options'] ?? [];
        } else {
            $options      = $this->getOptions($style['options'] ?? []);
            $imageContext = $this->imageHandler->getContext($data['value'], $options);
            unset($style['options']);
            $this->addStyle($style['container'] ?? []);

            foreach ($imageContext->getSources() as $source) {
                $sourceEl = new Source($source->type);
                foreach ($source->srcset as $srcset) {
                    $sourceEl->addSrcset($srcset->variant, $basePath.'/img/'.$srcset->src);
                }
                foreach ($options['sizes'] as $size) {
                    $sourceEl->addSize($size);
                }
                $this->addChild($sourceEl);
            }
        }

        $img = new Element('img', true, true);
        $img->setAttribute('src', $basePath.'/img/'.$imageContext->getSrc());
        $img->setAttribute('loading', $options['loading'] ?? 'lazy');
        $img->setAttribute('alt', $data['alt'] ?? '');
        $img->setAttribute('width', (string) $imageContext->getWidth());
        $img->setAttribute('height', (string) $imageContext->getHeight());
        $img->addStyle($style);
        $this->addChild($img);
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
