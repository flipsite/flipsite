<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ColorHelper;

abstract class AbstractComponent extends AbstractElement
{
    use Traits\ImageHandlerTrait;
    use Traits\SiteDataTrait;

    abstract public function build(array $data, array $style, array $options) : void;

    public function normalize(string|int|bool|array $data) : array
    {
        return is_array($data) ? $data : ['value' => $data];
    }

    public function setTag(string $tag) : void
    {
        $this->tag = $tag;
    }

    public function setBackground(AbstractElement $target, array $style) : void
    {
        $src      = $style['src'] ?? false;
        $gradient = $this->parseThemeColors($style['gradient'] ?? '');
        $options  = $style['options'] ?? [];
        $options['width'] ??= 512;
        $options['srcset'] ??= ['1x', '2x'];
        $style['position'] ??= 'bg-center';
        $style['size'] ??= 'bg-cover';
        $style['repeat'] ??= 'bg-no-repeat';
        unset($style['src'],$style['gradient'],$style['options']);
        if ($src) {
            if (strlen($gradient)) {
                $gradient.=',';
            }
            if ($this->isSvg($src)) {
                $imageContext = $this->imageHandler->getContext($src, []);
                $target->setAttribute('style', 'background-image:'.$gradient.'url('.$imageContext->getSrc().');');
            } else {
                if (($options['webp'] ?? true)) {
                    $src = str_replace('.jpg', '.webp', $src);
                    $src = str_replace('.png', '.webp', $src);
                }
                $imageContext = $this->imageHandler->getContext($src, $options);
                $srcset       = $imageContext->getSrcset('url');
                if (null !== $srcset) {
                    $target->setAttribute('style', 'background-image:'.$gradient.'-webkit-image-set('.$srcset.')');
                } else {
                    // Missing image
                    $target->setAttribute('style', 'background-color:#EF4444');
                }
            }
            if (($style['options']['loading'] ?? '') === 'eager') {
                $this->builder->dispatch(new Event('preload', 'background', $imageContext));
            }
            unset($style['options']);
        } elseif ($gradient) {
            $target->setAttribute('style', 'background-image:'.$gradient);
            unset($style['options']);
        } else {
            unset($style['options'], $style['position'], $style['size'], $style['repeat']);
        }
        foreach ($style as $attr => $val) {
            $target->addStyle(['bg.'.$attr => $val]);
        }
    } 

    private function isSvg(string $filename) : bool
    {
        return false !== mb_strpos($filename, '.svg');
    }
    private function parseThemeColors(string $gradient) : string {
        if (!strlen($gradient)) {
            return $gradient;
        }
        $colors = $this->reader->get('theme.colors');
        $colors['white'] = '#ffffff';
        $colors['black'] = '#000000';
        return ColorHelper::parseAndReplace($gradient, $colors);
    }
}
