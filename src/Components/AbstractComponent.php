<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

abstract class AbstractComponent extends AbstractElement
{
    abstract public function build(array $data, array $style, string $appearance) : void;

    public function normalize(string|int|bool|array $data) : array
    {
        return is_array($data) ? $data : ['value' => $data];
    }

    public function setBackground(string|array $background, array $style = []) : void
    {
        if (is_string($background)) {
            $background = ['src' => $background];
        }
        if (isset($background['style'])) {
            $style = ArrayHelper::merge($style, $background['style']);
        }
        $src     = $background['src'];
        $options = $style['options'] ?? [];

        if (isset($background['style'])) {
            $style = ArrayHelper::merge($style, $background['style']);
            unset($background['style']);
        }

        if ($this->isSvg($src)) {
            $imageContext = $this->imageHandler->getContext($src, []);
            $this->setAttribute('style', 'background-image:url('.$imageContext->getSrc().');');
        } else {
            if ($this->canIUse->webp()) {
                $src = str_replace('.jpg', '.webp', $src);
                $src = str_replace('.png', '.webp', $src);
            }
            $imageContext = $this->imageHandler->getContext($src, $options);
            $this->setAttribute('style', 'background-image:url('.$imageContext->getSrc().');background-image:image-set('.$imageContext->getSrcset('url').')');
        }
        // if ($isEager) {
        //     $this->builder->dispatch(new Event('preload', 'image', $this));
        // }

        unset($style['options']);
        $this->addStyle($style);
    }

    private function isSvg(string $filename) : bool
    {
        return false !== mb_strpos($filename, '.svg');
    }
}
