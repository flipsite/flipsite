<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait BackgroundTrait
{
    public function handleBackground(AbstractElement &$element, array $style): void
    {
        $src      = $style['src'] ?? false;
        $gradient = $this->parseThemeColors($style['gradient'] ?? '');
        $options  = $style['options'] ?? [];
        $options['width'] ??= 512;
        $options['srcset'] ??= ['1x', '2x'];
        $options['webp'] ??= true;
        $style['position'] ??= 'bg-center';
        $style['size'] ??= 'bg-cover';
        $style['repeat'] ??= 'bg-no-repeat';
        unset($style['src'],$style['gradient'],$style['options']);
        if ($src) {
            $imageAttributes = $this->assets->getImageAttributes($src, $options);
            if (strlen($gradient)) {
                $gradient .= ',';
            }
            // SVG
            if (str_ends_with($src, '.svg')) {
                $element->setAttribute('style', 'background-image:' . $gradient . 'url(' . $imageAttributes->getSrc() . ');');
            } elseif ($imageAttributes && $srcset = $imageAttributes->getSrcset('url')) {
                $element->setAttribute('style', 'background-image:' . $gradient . '-webkit-image-set(' . $srcset . ')');
            }
            if (($style['options']['loading'] ?? '') === 'eager') {
                $this->builder->dispatch(new Event('preload', 'background', $imageAttributes));
            }
        } elseif ($gradient) {
            $element->setAttribute('style', 'background-image:' . $gradient);
            unset($style['options']);
        } else {
            unset($style['options'], $style['position'], $style['size'], $style['repeat']);
        }
        foreach ($style as $attr => $val) {
            $element->addStyle(['bg.' . $attr => $val]);
        }
    }
}
