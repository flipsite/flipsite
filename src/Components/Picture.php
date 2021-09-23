<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Picture extends AbstractComponent
{
    use Traits\EnviromentTrait;
    use Traits\ImageHandlerTrait;
    protected string $tag = 'picture';

    public function with(ComponentData $data) : void
    {
        $basePath = $this->enviroment->getBasePath();
        $src      = $data->get('value');
        $options  = $this->getOptions($data->getStyle('options'));
        $this->addStyle($data->getStyle('container'));
        if ($this->isSvg($src)) {
            $imageContext = $this->imageHandler->getContext($src, []);
        } else {
            $imageContext = $this->imageHandler->getContext($src, $options);
            foreach ($imageContext->getSources() as $source) {
                $sourceEl = new Source($source->type);
                foreach ($source->srcset as $srcset) {
                    $sourceEl->addSrcset($srcset->variant, $basePath.'/img/'.$srcset->src);
                }
                foreach ($options['sizes'] ?? [] as $size) {
                    $sourceEl->addSize($size);
                }
                $this->addChild($sourceEl);
            }
        }
        $img = new Element('img', true, true);
        $img->setAttribute('src', $basePath.'/img/'.$imageContext->getSrc());
        $img->setAttribute('loading', $options['loading'] ?? 'lazy');
        $img->setAttribute('alt', $data->get('alt'));
        $img->setAttribute('width', (string) $imageContext->getWidth());
        $img->setAttribute('height', (string) $imageContext->getHeight());
        $img->addStyle($data->getStyle());
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
