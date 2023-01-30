<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractComponent extends AbstractElement
{
    use Traits\ImageHandlerTrait;

    abstract public function build(array $data, array $style, string $appearance) : void;

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
        $src     = $style['src'] ?? false;
        $options = $style['options'] ?? [];
        $options['width'] ??= 480;
        $options['srcset'] ??= ['1x', '2x'];
        $style['position'] ??= 'bg-center';
        $style['size'] ??= 'bg-cover';
        $style['repeat'] ??= 'bg-no-repeat';
        unset($style['src'],$style['options']);

        if ($src) {
            $gradient = '';
            foreach ($style as $key => &$val) {
                if (null === $val) {
                    continue;
                }
                $classes = explode(' ', $val);
                $new     = [];
                foreach ($classes as $cls) {
                    if (strpos($cls, 'bg-gradient') !== false) {
                        $gradient = $cls;
                    } else {
                        $new[] = $cls;
                    }
                }
                $val = count($new) ? implode(' ', $new) : null;
            }

            if ($gradient) {
                // TODO can this horrible hack be fixed? if bg gradient it needs to be added to element style
                $callback = new \Flipsite\Style\Callbacks\BgGradientCallback();
                $tmp      = explode('-', $gradient);
                array_shift($tmp);
                $gradient = $callback($tmp).',';
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
                $target->setAttribute('style', 'background-image:'.$gradient.'-webkit-image-set('.$imageContext->getSrcset('url').')');
            }
            if (($style['options']['loading'] ?? '') === 'eager') {
                $this->builder->dispatch(new Event('preload', 'background', $imageContext));
            }
            unset($style['options']);
        } else {
            unset($style['options'], $style['position'], $style['size'], $style['repeat']);
        }
        $target->addStyle($style);
    }

    private function isSvg(string $filename) : bool
    {
        return false !== mb_strpos($filename, '.svg');
    }
}
